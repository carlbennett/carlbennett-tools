<?php

namespace CarlBennett\Tools\Libraries;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Libraries\IDatabaseObject;

use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \LengthException;
use \PDO;
use \PDOException;
use \StdClass;
use \UnexpectedValueException;

class PasteObject implements IDatabaseObject {

  const DATE_SQL = 'Y-m-d H:i:s';

  # Maximum SQL field lengths, alter as appropriate.
  const MAX_CONTENT = 4294967295; // 4 GiB
  const MAX_TITLE   = 191;

  const OPTION_QUARANTINE = 0x00000001;
  const OPTION_UNLISTED   = 0x00000002;

  const UUID_REGEX = '/([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}/';

  private $_id;

  protected $content;
  protected $date_added;
  protected $date_expires;
  protected $id;
  protected $mimetype;
  protected $options_bitmask;
  protected $password_hash;
  protected $title;
  protected $user_id;

  public function __construct($value) {
    if (is_null($value) || is_string($value)) {
      $this->_id = $value;
      $this->allocate();
      return;
    }

    if ($value instanceof StdClass) {
      $this->allocateObject($value);
      return;
    }

    throw new InvalidArgumentException('value must be a string or StdClass');
  }

  public function allocate() {
    // from the IDatabaseObject interface
    $id = $this->_id;

    if (!(is_null($id) || is_string($id))) {
      throw new InvalidArgumentException('value must be null or a string');
    }

    $this->setDateAdded(new DateTime('now', new DateTimeZone('Etc/UTC')));

    if (empty($id)) return;

    $this->setId($id);

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT
        `content`, `date_added`, `date_expires`, UuidFromBin(`id`) AS `id`,
        `mimetype`, `options_bitmask`, `password_hash`, `title`,
        UuidFromBin(`user_id`) AS `user_id`
      FROM `pastebin` WHERE `id` = UuidToBin(:id) LIMIT 1;
    ');
    $q->bindParam(':id', $id, PDO::PARAM_STR);

    $r = $q->execute();
    if (!$r) {
      throw new UnexpectedValueException('an error occurred finding the paste');
    }

    if ($q->rowCount() != 1) {
      throw new UnexpectedValueException(sprintf(
        'paste id: %s not found', $id
      ));
    }

    $r = $q->fetchObject();
    $q->closeCursor();

    $this->allocateObject($r);
  }

  protected function allocateObject(StdClass $value) {
    $tz = new DateTimeZone('Etc/UTC');

    $date_expires = (
      is_null($value->date_expires) ?
      null : new DateTime($value->date_expires, $tz)
    );

    $this->setContent($value->content);
    $this->setDateAdded(new DateTime($value->date_added, $tz));
    $this->setDateExpires($date_expires);
    $this->setId($value->id);
    $this->setMimetype($value->mimetype);
    $this->setOptionsBitmask($value->options_bitmask);
    $this->setPasswordHash($value->password_hash);
    $this->setTitle($value->title);
    $this->setUserId($value->user_id);
  }

  public function checkPassword(string $password) {
    $hash = $this->getPasswordHash();
    $rehash = password_needs_rehash($hash, PASSWORD_BCRYPT, array(
      'cost' => Common::$config->pastes->bcrypt_cost,
    ));
    $verified = password_verify($password, $hash);

    if ($rehash && $verified) {
      $this->setPasswordHash(self::createPassword($password));
      $this->commit();
    }

    return $verified;
  }

  public function commit() {
    // from the IDatabaseObject interface

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    if (empty($this->id)) {
      $q = Common::$database->query('SELECT UUID();');
      if (!$q) return $q;

      $this->id = $q->fetch(PDO::FETCH_NUM)[0];
      $q->closeCursor();
    }

    $q = Common::$database->prepare('
      INSERT INTO `pastebin` (
        `content`, `date_added`, `date_expires`, `id`, `mimetype`,
        `options_bitmask`, `password_hash`, `title`, `user_id`
      ) VALUES (
        :content, :added, :expires, UuidToBin(:id), :mimetype,
        :options, :password, :title, UuidToBin(:user_id)
      ) ON DUPLICATE KEY UPDATE
        `content` = :content, `date_added` = :added, `date_expires` = :expires,
        `mimetype` = :mimetype, `options_bitmask` = :options,
        `password_hash` = :password, `title` = :title,
        `user_id` = UuidToBin(:user_id)
      ;
    ');

    $date_added = $this->date_added->format(self::DATE_SQL);
    $date_expires = (
      is_null($this->date_expires) ? null :
      $this->date_expires->format(self::DATE_SQL)
    );

    $q->bindParam(':added', $date_added, PDO::PARAM_STR);
    $q->bindParam(':content', $this->content, PDO::PARAM_STR);

    $q->bindParam(':expires', $date_expires, (
      is_null($date_expires) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':id', $this->id, PDO::PARAM_STR);
    $q->bindParam(':mimetype', $this->mimetype, PDO::PARAM_STR);
    $q->bindParam(':options', $this->options_bitmask, PDO::PARAM_INT);

    $q->bindParam(':password', $this->password_hash, (
      is_null($this->password_hash) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':title', $this->title, PDO::PARAM_STR);

    $q->bindParam(':user_id', $this->user_id, (
      is_null($this->user_id) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $r = $q->execute();
    if (!$r) return $r;

    $q->closeCursor();
    return $r;
  }

  public static function createPassword(string $password) {
    if (!is_string($password)) {
      throw new InvalidArgumentException('value must be a string');
    }

    if (empty($password)) {
      throw new LengthException('value must not be empty');
    }

    return password_hash($password, PASSWORD_BCRYPT, array(
      'cost' => Common::$config->pastes->bcrypt_cost,
    ));
  }

  public function getContent() {
    return $this->content;
  }

  public function getDateAdded() {
    return $this->date_added;
  }

  public function getDateExpires() {
    return $this->date_expires;
  }

  public function getId() {
    return $this->id;
  }

  public function getMimetype() {
    return $this->mimetype;
  }

  public function getOptionsBitmask() {
    return $this->options_bitmask;
  }

  public function getPasswordHash() {
    return $this->password_hash;
  }

  public static function getRecentPastes($limit = 10, $bitmask = null, $passworded = false) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    if (is_null($bitmask) || !is_numeric($bitmask)) {
      $bitmask = (self::OPTION_QUARANTINE | self::OPTION_UNLISTED);
    }

    $q = Common::$database->prepare(sprintf('
      SELECT UuidFromBin(`id`) AS `id` FROM `pastebin`
      WHERE %s NOT (`options_bitmask` & %d)
      ORDER BY `date_added` DESC LIMIT %d;
    ', (!$passworded ? '`password_hash` IS NULL AND' : ''), $bitmask, $limit));

    $r = $q->execute();
    if (!$r) {
      throw new UnexpectedValueException(
        'an error occurred finding public pastes'
      );
    }

    $pastes = array();
    while ($r = $q->fetchObject()) {
      $pastes[] = new self($r->id);
    }
    $q->closeCursor();

    return $pastes;
  }

  public function getTitle() {
    return $this->title;
  }

  public function getURI() {
    return Common::relativeUrlToAbsolute('/paste/' . $this->id);
  }

  public function getUser() {
    return (
      is_null($this->user_id) ? $this->user_id : new User($this->user_id)
    );
  }

  public function getUserId() {
    return $this->user_id;
  }

  public function setContent(string $value) {
    if (!is_string($value)) {
      throw new InvalidArgumentException('value must be a string');
    }

    $this->content = $value;
  }

  public function setDateAdded(DateTime $value) {
    if (!$value instanceof DateTime) {
      throw new InvalidArgumentException(
        'value must be a DateTime object'
      );
    }

    $this->date_added = $value;
  }

  public function setDateExpires(?DateTime $value) {
    if (!(is_null($value) || $value instanceof DateTime)) {
      throw new InvalidArgumentException(
        'value must be null or a DateTime object'
      );
    }

    $this->date_expires = $value;
  }

  public function setId(string $value) {
    if (!is_string($value) || preg_match(self::UUID_REGEX, $value) !== 1) {
      throw new InvalidArgumentException(
        'value must be a string in UUID format'
      );
    }

    $this->id = $value;
  }

  public function setMimetype(string $value) {
    if (!is_string($value) || empty($value)) {
      throw new InvalidArgumentException(
        'value must be a non-empty string in mimetype format'
      );
    }

    $this->mimetype = $value;
  }

  public function setOptionsBitmask(int $value) {
    if (!is_int($value)) {
      throw new InvalidArgumentException(
        'value must be an integer'
      );
    }

    $this->options_bitmask = $value;
  }

  public function setPasswordHash(?string $value) {
    if (!(is_null($value) || is_string($value))) {
      throw new InvalidArgumentException('value must be null or a string');
    }

    if (!is_null($value) && empty($value)) {
      throw new LengthException('value must be non-empty');
    }

    $this->password_hash = $value;
  }

  public function setTitle(string $value) {
    if (!is_string($value)) {
      throw new InvalidArgumentException('value must be a string');
    }

    $this->title = $value;
  }

  public function setUser(?User $value) {
    if (!(is_null($value) || $value instanceof User)) {
      throw new InvalidArgumentException(
        'value must be null or instance of User'
      );
    }

    $this->user_id = (is_null($value) ? $value : $value->getId());
  }

  public function setUserId(?string $value) {
    if (!(is_null($value)
      || (is_string($value) && preg_match(self::UUID_REGEX, $value) === 1))) {
      throw new InvalidArgumentException(
        'value must be null, or a string in UUID format'
      );
    }

    $this->user_id = $value;
  }
}
