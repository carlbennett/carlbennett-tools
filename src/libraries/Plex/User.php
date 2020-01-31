<?php

namespace CarlBennett\Tools\Libraries\Plex;

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

  const DATE_SQL = 'Y-m-d H:i:s';

  const MAX_EMAIL    = 191;
  const MAX_NOTES    = 65535;
  const MAX_USERNAME = 191;

  const RISK_UNASSESSED = 0;
  const RISK_LOW        = 1;
  const RISK_MEDIUM     = 2;
  const RISK_HIGH       = 3;

  const UUID_REGEX = '/([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}/';

  private $_id;

  protected $date_added;
  protected $date_removed;
  protected $email;
  protected $id;
  protected $notes;
  protected $risk;
  protected $username;

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
    $this->setRisk(self::RISK_UNASSESSED);

    if (empty($id)) return;

    $this->setId($id);

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT
        `date_added`, `date_removed`, `email`, UuidFromBin(`id`) AS `id`,
        `notes`, `risk`, `username`
      FROM `plex_users` WHERE id = UuidToBin(:id) LIMIT 1;
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
    $this->setEmail($value->email);
    $this->setId($value->id);
    $this->setNotes($value->notes);
    $this->setRisk($value->risk);
    $this->setUsername($value->username);
  }

  public function commit() {
    // from the IDatabaseObject interface

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      INSERT INTO `plex_users` (
        `date_added`, `date_removed`, `email`, `id`, `notes`, `risk`,
        `username`
      ) VALUES (
        :added, :removed, :email, UuidToBin(:id), :notes, :risk, :username
      ) ON DUPLICATE KEY UPDATE
        `date_added` = :added, `date_removed` = :removed, `email` = :email,
        `notes` = :notes, `risk` = :risk, `username` = :username
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

    $q->bindParam(':email', $this->email, (
      is_null($this->email) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':id', $this->id, PDO::PARAM_STR);

    $q->bindParam(':notes', $this->notes, (
      is_null($this->notes) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':risk', $this->risk, PDO::PARAM_INT);

    $q->bindParam(':username', $this->username, (
      is_null($this->username) ? PDO::PARAM_NULL : PDO::PARAM_STR
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
      SELECT
        `date_added`, `date_removed`, `email`, UuidFromBin(`id`) AS `id`,
        `notes`, `risk`, `username`
      FROM `plex_users`
      ORDER BY `date_added`, `username`, `email`;
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

  public function getEmail() {
    return $this->email;
  }

  public function getId() {
    return $this->id;
  }

  public function getNotes() {
    return $this->notes;
  }

  public function getRisk() {
    return $this->risk;
  }

  public function getUsername() {
    return $this->username;
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

  public function setRisk(int $value) {
    if (!is_int($value) || $value < 0 || $value > 3) {
      throw new InvalidArgumentException(
        'value must be an integer between range 0-3'
      );
    }

    $this->risk = $value;
  }

  public function setUsername($value) {
    if (!(is_null($value) || is_string($value))) {
      throw new InvalidArgumentException(
        'value must be null or a string'
      );
    }

    if (is_string($value) && strlen($value) > self::MAX_USERNAME) {
      throw new LengthException(sprintf(
        'value must be less than or equal to %d characters', self::MAX_USERNAME
      ));
    }

    $this->username = $value;
  }

}
