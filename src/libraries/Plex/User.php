<?php

namespace CarlBennett\Tools\Libraries\Plex;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\DateTime;
use \CarlBennett\Tools\Libraries\BaseUser;
use \CarlBennett\Tools\Libraries\IDatabaseObject;

use \DateTimeZone;
use \InvalidArgumentException;
use \LengthException;
use \PDO;
use \PDOException;
use \StdClass;
use \UnexpectedValueException;

class User implements IDatabaseObject {

  const DATE_SQL = 'Y-m-d H:i:s';

  const MAX_NOTES         = 65535;
  const MAX_PLEX_EMAIL    = 191;
  const MAX_PLEX_USERNAME = 191;

  const RISK_UNASSESSED = 0;
  const RISK_LOW        = 1;
  const RISK_MEDIUM     = 2;
  const RISK_HIGH       = 3;

  const UUID_REGEX = '/([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}/';

  private $_id;

  protected $date_added;
  protected $date_removed;
  protected $id;
  protected $notes;
  protected $plex_email;
  protected $plex_username;
  protected $risk;
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

    $this->setRisk(self::RISK_UNASSESSED);

    if (empty($id)) return;

    $this->setId($id);

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT `date_added`, `date_removed`, UuidFromBin(`id`) AS `id`, `notes`,
             `plex_email`, `plex_username`, `risk`,
             UuidFromBin(`user_id`) AS `user_id`
      FROM `plex_users` WHERE `id` = UuidToBin(:id) LIMIT 1;
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
    $this->setDateRemoved(
      $value->date_removed ? new DateTime($value->date_removed, $tz) : null
    );
    $this->setId($value->id);
    $this->setNotes($value->notes);
    $this->setPlexEmail($value->plex_email);
    $this->setPlexUsername($value->plex_username);
    $this->setRisk($value->risk);
    $this->setUserId($value->user_id);
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
      INSERT INTO `plex_users` (
        `date_added`, `date_removed`, `id`, `notes`, `plex_email`,
        `plex_username`, `risk`, `user_id`
      ) VALUES (
        :added, :removed, UuidToBin(:id), :notes, :plex_email, :plex_username,
        :risk, UuidToBin(:user_id)
      ) ON DUPLICATE KEY UPDATE
        `date_added` = :added, `date_removed` = :removed, `notes` = :notes,
        `plex_email` = :plex_email, `plex_username` = :plex_username,
        `risk` = :risk, `user_id` = UuidToBin(:user_id)
      ;
    ');

    $date_added = $this->date_added->format(self::DATE_SQL);

    $date_removed = (
      is_null($this->date_removed) ?
      null : $this->date_removed->format(self::DATE_SQL)
    );

    $q->bindParam(':added', $date_added, PDO::PARAM_STR);

    $q->bindParam(':removed', $date_removed, (
      is_null($date_removed) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':id', $this->id, PDO::PARAM_STR);
    $q->bindParam(':notes', $this->notes, PDO::PARAM_STR);

    $q->bindParam(':plex_email', $this->plex_email, (
      is_null($this->plex_email) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':plex_username', $this->plex_username, (
      is_null($this->plex_username) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':risk', $this->risk, PDO::PARAM_INT);

    $q->bindParam(':user_id', $this->user_id, (
      is_null($this->user_id) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $r = $q->execute();
    if (!$r) return $r;

    $q->closeCursor();
    return $r;
  }

  public static function getAll() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT `date_added`, `date_removed`, UuidFromBin(`id`) AS `id`, `notes`,
             `plex_email`, `plex_username`, `risk`,
             UuidFromBin(`user_id`) AS `user_id`
      FROM `plex_users` ORDER BY `date_added`, `plex_username`, `plex_email`;
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

  public function getDateAdded() {
    return $this->date_added;
  }

  public function getDateRemoved() {
    return $this->date_removed;
  }

  public function getId() {
    return $this->id;
  }

  public function getNotes() {
    return $this->notes;
  }

  public function getPlexEmail() {
    return $this->plex_email;
  }

  public function getPlexUsername() {
    return $this->plex_username;
  }

  public function getRisk() {
    return $this->risk;
  }

  public function getUser() {
    return (
      is_null($this->user_id) ? $this->user_id : new BaseUser($this->user_id)
    );
  }

  public function getUserId() {
    return $this->user_id;
  }

  public function setDateAdded(DateTime $value) {
    if (!$value instanceof DateTime) {
      throw new InvalidArgumentException(
        'value must be a DateTime object'
      );
    }

    $this->date_added = $value;
  }

  public function setDateRemoved($value) {
    if (!(is_null($value) || $value instanceof DateTime)) {
      throw new InvalidArgumentException(
        'value must be null or a DateTime object'
      );
    }

    $this->date_removed = $value;
  }

  public function setId(string $value) {
    if (!is_string($value) || preg_match(self::UUID_REGEX, $value) !== 1) {
      throw new InvalidArgumentException(
        'value must be a string in UUID format'
      );
    }

    $this->id = $value;
  }

  public function setNotes(string $value) {
    if (!is_string($value)) {
      throw new InvalidArgumentException(
        'value must be a string'
      );
    }

    if (strlen($value) > self::MAX_NOTES) {
      throw new LengthException(sprintf(
        'value must be less than or equal to %d characters', self::MAX_NOTES
      ));
    }

    $this->notes = $value;
  }

  public function setPlexEmail($value, $auto_null = true) {
    if (!(is_null($value) || is_string($value))) {
      throw new InvalidArgumentException(
        'value must be null or a string'
      );
    }

    if ($auto_null && is_string($value) && empty($value)) { $value = null; }

    if (is_string($value) && strlen($value) > self::MAX_PLEX_EMAIL) {
      throw new LengthException(sprintf(
        'value must be less than or equal to %d characters',
        self::MAX_PLEX_EMAIL
      ));
    }

    $this->plex_email = $value;
  }

  public function setPlexUsername($value, $auto_null = true) {
    if (!(is_null($value) || is_string($value))) {
      throw new InvalidArgumentException(
        'value must be null or a string'
      );
    }

    if ($auto_null && is_string($value) && empty($value)) { $value = null; }

    if (is_string($value) && strlen($value) > self::MAX_PLEX_USERNAME) {
      throw new LengthException(sprintf(
        'value must be less than or equal to %d characters',
        self::MAX_PLEX_USERNAME
      ));
    }

    $this->plex_username = $value;
  }

  public function setRisk(int $value) {
    if (!is_int($value) || $value < 0 || $value > 3) {
      throw new InvalidArgumentException(
        'value must be an integer between range 0-3'
      );
    }

    $this->risk = $value;
  }

  public function setUser(BaseUser $value) {
    return $this->setUserId(
      is_null($value) ? $value : $value->getId()
    );
  }

  public function setUserId($value) {
    if (!(is_null($value) || is_string($value)
      || preg_match(self::UUID_REGEX, $value) === 1)) {
      throw new InvalidArgumentException(
        'value must be null or a string in UUID format'
      );
    }

    $this->user_id = $value;
  }

}
