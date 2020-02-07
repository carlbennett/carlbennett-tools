<?php

namespace CarlBennett\Tools\Libraries;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\Tools\Libraries\IDatabaseObject;

use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \LengthException;
use \PDO;
use \PDOException;
use \StdClass;
use \UnexpectedValueException;

class User implements IDatabaseObject {

  const DEFAULT_OPTION   = 0x00000000;
  const DEFAULT_TIMEZONE = 'America/Chicago';

  const MAX_DISPLAY_NAME = 191;
  const MAX_EMAIL        = 191;

  const OPTION_DISABLED          = 0x00000001;
  const OPTION_ACL_PLEX_REQUESTS = 0x00000010;
  const OPTION_ACL_PLEX_USERS    = 0x00000020;
  const OPTION_RESERVED          = 0xFFFFFFCE;

  const PASSWORD_CHECK_VERIFIED = 1;
  const PASSWORD_CHECK_EXPIRED  = 2;

  const UUID_REGEX = '/([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}/';

  private $_id;

  protected $date_added;
  protected $display_name;
  protected $email;
  protected $id;
  protected $options_bitmask;
  protected $password_hash;
  protected $timezone;

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
    $this->setOptionsBitmask(self::DEFAULT_OPTION);

    if (empty($id)) return;

    $this->setId($id);

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT
        `date_added`, `display_name`, `email`, UuidFromBin(`id`) AS `id`,
        `options_bitmask`, `password_hash`, `timezone`
      FROM `users` WHERE id = UuidToBin(:id) LIMIT 1;
    ');
    $q->bindParam(':id', $id, PDO::PARAM_STR);

    $r = $q->execute();
    if (!$r) {
      throw new UnexpectedValueException('an error occurred finding user id');
    }

    if ($q->rowCount() != 1) {
      throw new UnexpectedValueException(sprintf(
        'user id: %s not found', $id
      ));
    }

    $r = $q->fetchObject();
    $q->closeCursor();

    $this->allocateObject($r);
  }

  protected function allocateObject(StdClass $value) {
    $tz = new DateTimeZone('Etc/UTC');

    $this->setDateAdded(new DateTime($value->date_added), $tz);
    $this->setEmail($value->email);
    $this->setId($value->id);
    $this->setName($value->display_name);
    $this->setOptionsBitmask($value->options_bitmask);
    $this->setPasswordHash($value->password_hash);
    $this->setTimezone($value->timezone);
  }

  public function checkPassword(string $password) {
    $hash = $this->getPasswordHash();
    $rehash = password_needs_rehash($hash, PASSWORD_BCRYPT, array(
      'cost' => Common::$config->users->bcrypt_cost,
    ));
    $verified = password_verify($password, $hash);

    $r = 0;

    if ($verified) $r |= self::PASSWORD_CHECK_VERIFIED;
    if ($rehash)   $r |= self::PASSWORD_CHECK_EXPIRED;

    return $r;
  }

  public function commit() {
    // from the IDatabaseObject interface
    throw new \RuntimeException('TODO');
  }

  public static function createPassword(string $password) {
    if (!is_string($password)) {
      throw new InvalidArgumentException('value must be a string');
    }

    if (empty($password)) {
      throw new LengthException('value must not be empty');
    }

    return password_hash($password, PASSWORD_BCRYPT, array(
      'cost' => Common::$config->users->bcrypt_cost,
    ));
  }

  public static function getAll() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT
        `date_added`, `display_name`, `email`, UuidFromBin(`id`) AS `id`,
        `options_bitmask`, `password_hash`, `timezone`
      FROM `users`
      ORDER BY `date_added`, `display_name`, `email`;
    ');

    $r = $q->execute();
    if (!$r) return $r;

    $r = array();
    while ($obj = $q->fetchObject()) {
      $r[] = new self($obj);
    }

    $q->closeCursor();
    return $r;
  }

  public static function getByEmail(string $value) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT
        `date_added`, `display_name`, `email`, UuidFromBin(`id`) AS `id`,
        `options_bitmask`, `password_hash`, `timezone`
      FROM `users` WHERE `email` = :email;
    ');
    $q->bindParam(':email', $value, PDO::PARAM_STR);

    $r = $q->execute();
    if (!$r) return $r;

    if ($q->rowCount()) {
      $r = new self($q->fetchObject());
    } else {
      $r = false;
    }

    $q->closeCursor();
    return $r;
  }

  public function getDateAdded() {
    return $this->date_added;
  }

  public function getEmail() {
    return $this->email;
  }

  public function getId() {
    return $this->id;
  }

  public function getName() {
    return $this->display_name;
  }

  public function getOption(int $value) {
    if (!is_int($value)) {
      throw new InvalidArgumentException('value must be an int');
    }

    return ($this->options_bitmask & $value);
  }

  public function getOptionsBitmask() {
    return $this->options_bitmask;
  }

  public function getPasswordHash() {
    return $this->password_hash;
  }

  public function getTimezone() {
    return $this->timezone;
  }

  public function setDateAdded(DateTime $value) {
    if (!$value instanceof DateTime) {
      throw new InvalidArgumentException(
        'value must be a DateTime object'
      );
    }

    $this->date_added = $value;
  }

  public function setEmail($value) {
    if (!(is_null($value) || is_string($value))) {
      throw new InvalidArgumentException(
        'value must be null or a string'
      );
    }

    if (is_string($value) && strlen($value) > self::MAX_EMAIL) {
      throw new LengthException(sprintf(
        'email must be less than or equal to %d characters', self::MAX_EMAIL
      ));
    }

    $this->email = $value;
  }

  public function setId(string $value) {
    if (!is_string($value) || preg_match(self::UUID_REGEX, $value) !== 1) {
      throw new InvalidArgumentException(
        'value must be a string in UUID format'
      );
    }

    $this->id = $value;
  }

  public function setName(string $value) {
    if (!is_string($value)) {
      throw new InvalidArgumentException(
        'value must be a string'
      );
    }

    if (strlen($value) > self::MAX_DISPLAY_NAME) {
      throw new LengthException(sprintf(
        'value must be less than or equal to %d characters',
        self::MAX_DISPLAY_NAME
      ));
    }

    $this->display_name = $value;
  }

  public function setOption(int $option, bool $value) {
    if (!is_int($option)) {
      throw new InvalidArgumentException('option must be an int');
    }

    if (!is_bool($value)) {
      throw new InvalidArgumentException('value must be a bool');
    }

    if ($value) {
      $this->options_bitmask |= $option;
    } else {
      $this->options_bitmask &= ~$option;
    }
  }

  public function setOptionsBitmask(int $value) {
    if (!is_int($value) || $value < 0) {
      throw new InvalidArgumentException('value must be a positive integer');
    }

    $this->options_bitmask = $value;
  }

  public function setPasswordHash(string $value) {
    if (!is_string($value)) {
      throw new InvalidArgumentException('value must be a string');
    }

    if (empty($value)) {
      throw new LengthException('value must be non-empty');
    }

    $this->password_hash = $value;
  }

  public function setTimezone(string $value) {
    if (!is_string($value)) {
      throw new InvalidArgumentException('value must be a string');
    }

    if (empty($value)) {
      throw new LengthException('value must be non-empty');
    }

    $this->timezone = $value;
  }

}
