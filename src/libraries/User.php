<?php

namespace CarlBennett\Tools\Libraries;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\Tools\Libraries\IDatabaseObject;
use \CarlBennett\Tools\Libraries\User\Invite as Invitation;

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

  const DEFAULT_OPTION   = 0x00000000;
  const DEFAULT_TIMEZONE = 'Etc/UTC';

  # Maximum SQL field lengths, alter as appropriate.
  const MAX_DISPLAY_NAME      = 191;
  const MAX_EMAIL             = 191;
  const MAX_INTERNAL_NOTES    = 65535;
  const MAX_INVITES_AVAILABLE = 65535;
  const MAX_TIMEZONE          = 191;

  const OPTION_DISABLED           = 0x00000001;
  const OPTION_BANNED             = 0x00000002;
  const OPTION_RESERVED_0         = 0x00000004;
  const OPTION_ACL_PASTEBIN_ADMIN = 0x00000008;
  const OPTION_ACL_PLEX_REQUESTS  = 0x00000010;
  const OPTION_ACL_PLEX_USERS     = 0x00000020;
  const OPTION_ACL_INVITE_USERS   = 0x00000040;
  const OPTION_ACL_PHPINFO        = 0x00000080;

  const PASSWORD_CHECK_VERIFIED = 1;
  const PASSWORD_CHECK_EXPIRED  = 2;

  const UUID_REGEX = '/([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}/';

  private $_id;

  protected $date_added;
  protected $date_banned;
  protected $date_disabled;
  protected $display_name;
  protected $email;
  protected $id;
  protected $internal_notes;
  protected $invites_available;
  protected $options;
  protected $password_hash;
  protected $record_updated;
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

    if (empty($id)) return;

    $this->setId($id);

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT
        `date_added`, `date_banned`, `date_disabled`, `display_name`, `email`,
        UuidFromBin(`id`) AS `id`, `internal_notes`, `invites_available`,
        `options`, `password_hash`, `record_updated`, `timezone`
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

    $this->setDateAdded(new DateTime($value->date_added, $tz));
    $this->setDateBanned(
      $value->date_banned ? new DateTime($value->date_banned, $tz) : null
    );
    $this->setDateDisabled(
      $value->date_disabled ? new DateTime($value->date_disabled, $tz) : null
    );
    $this->setEmail($value->email);
    $this->setId($value->id);
    $this->setInternalNotes($value->internal_notes);
    $this->setInvitesAvailable($value->invites_available);
    $this->setName($value->display_name);
    $this->setOptions($value->options);
    $this->setPasswordHash($value->password_hash);
    $this->setRecordUpdated(new DateTime($value->record_updated, $tz));
    $this->setTimezone($value->timezone);
  }

  public function checkPassword(string $password) {
    $cost = Common::$config->users->crypt_cost;
    $hash = $this->getPasswordHash();
    $rehash = password_needs_rehash(
      $hash, PASSWORD_BCRYPT, array('cost' => $cost)
    );
    $verified = password_verify($password, $hash);

    $r = 0;

    if ($verified) $r |= self::PASSWORD_CHECK_VERIFIED;
    if ($rehash)   $r |= self::PASSWORD_CHECK_EXPIRED;

    return $r;
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
      INSERT INTO `users` (
        `date_added`, `date_banned`, `date_disabled`, `display_name`, `email`,
        `id`, `internal_notes`, `invites_available`, `options`, `password_hash`,
        `record_updated`, `timezone`
      ) VALUES (
        :added, :banned, :disabled, :name, :email, UuidToBin(:id), :int_notes,
        :invites_a, :options, :password, :record_updated, :tz
      ) ON DUPLICATE KEY UPDATE
        `date_added` = :added, `date_banned` = :banned,
        `date_disabled` = :disabled, `display_name` = :name, `email` = :email,
        `internal_notes` = :int_notes, `invites_available` = :invites_a,
        `options` = :options, `password_hash` = :password,
        `record_updated` = :record_updated, `timezone` = :tz
      ;
    ');

    $date_added = $this->date_added->format(self::DATE_SQL);

    $date_banned = (
      is_null($this->date_banned) ?
      null : $this->date_banned->format(self::DATE_SQL)
    );

    $date_disabled = (
      is_null($this->date_disabled) ?
      null : $this->date_disabled->format(self::DATE_SQL)
    );

    $record_updated = $this->record_updated->format(self::DATE_SQL);

    $q->bindParam(':added', $date_added, PDO::PARAM_STR);

    $q->bindParam(':banned', $date_banned, (
      is_null($date_banned) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':disabled', $date_disabled, (
      is_null($date_disabled) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':email', $this->email, PDO::PARAM_STR);
    $q->bindParam(':id', $this->id, PDO::PARAM_STR);
    $q->bindParam(':int_notes', $this->internal_notes, PDO::PARAM_STR);
    $q->bindParam(':invites_a', $this->invites_available, PDO::PARAM_INT);
    $q->bindParam(':name', $this->display_name, PDO::PARAM_STR);
    $q->bindParam(':options', $this->options, PDO::PARAM_INT);
    $q->bindParam(':password', $this->password_hash, PDO::PARAM_STR);
    $q->bindParam(':record_updated', $record_updated, PDO::PARAM_STR);
    $q->bindParam(':tz', $this->timezone, PDO::PARAM_STR);

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

    $cost = Common::$config->users->crypt_cost;
    $digest = $password.$salt.$pepper;

    return password_hash($digest, PASSWORD_BCRYPT, array('cost' => $cost));
  }

  public static function getAll() {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT
        `date_added`, `date_banned`, `date_disabled`, `display_name`, `email`,
        UuidFromBin(`id`) AS `id`, `internal_notes`, `invites_available`,
        `options`, `password_hash`, `record_updated`, `timezone`
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
        `date_added`, `date_banned`, `date_disabled`, `display_name`, `email`,
        UuidFromBin(`id`) AS `id`, `internal_notes`, `invites_available`,
        `options`, `password_hash`, `record_updated`, `timezone`
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

  public function getDateBanned() {
    return $this->date_banned;
  }

  public function getDateDisabled() {
    return $this->date_disabled;
  }

  public function getEmail() {
    return $this->email;
  }

  public function getId() {
    return $this->id;
  }

  public function getInternalNotes() {
    return $this->internal_notes;
  }

  public function getInvitesAvailable() {
    return $this->invites_available;
  }

  public function getInvitesSent() {
    if (!isset($this->id) || empty($this->id)) {
      throw new InvalidArgumentException('id must be set prior to call');
    }

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare(
      'SELECT UuidFromBin(`id`) AS `id` FROM `user_invites`
      WHERE `invited_by` = UuidToBin(:id);'
    );
    $q->bindParam(':id', $this->id, PDO::PARAM_STR);

    $r = $q->execute();
    if (!$r) return $r;

    $invites = array();
    while ($row = $q->fetch(PDO::FETCH_NUM)) {
      $invites[] = new Invitation($row[0]);
    };

    $q->closeCursor();
    return $invites;
  }

  public function getInvitesUsed() {
    if (!isset($this->id) || empty($this->id)) {
      throw new InvalidArgumentException('id must be set prior to call');
    }

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare(
      'SELECT COUNT(*) FROM `user_invites` WHERE `invited_by` = UuidToBin(:id);'
    );
    $q->bindParam(':id', $this->id, PDO::PARAM_STR);

    $r = $q->execute();
    if (!$r) return $r;

    $r = $q->fetch(PDO::FETCH_NUM)[0];

    $q->closeCursor();
    return $r;
  }

  public function getName() {
    return $this->display_name;
  }

  public function getOption(int $option) {
    if (!is_int($option)) {
      throw new InvalidArgumentException('value must be an int');
    }

    return ($this->options & $option) === $option;
  }

  public function getOptions() {
    return $this->options;
  }

  public function getPasswordHash() {
    return $this->password_hash;
  }

  public function getRecordUpdated() {
    return $this->record_updated;
  }

  public function getTimezone() {
    return $this->timezone;
  }

  public function getTimezoneObject() {
    return new DateTimeZone($this->timezone);
  }

  public function isBanned() {
    return $this->getOption(self::OPTION_BANNED);
  }

  public function isDisabled() {
    return $this->getOption(self::OPTION_DISABLED);
  }

  public function setDateAdded(DateTime $value) {
    if (!$value instanceof DateTime) {
      throw new InvalidArgumentException(
        'value must be a DateTime object'
      );
    }

    $this->date_added = $value;
  }

  public function setDateBanned($value) {
    if (!(is_null($value) || $value instanceof DateTime)) {
      throw new InvalidArgumentException(
        'value must be null or a DateTime object'
      );
    }

    $this->date_banned = $value;
  }

  public function setDateDisabled($value) {
    if (!(is_null($value) || $value instanceof DateTime)) {
      throw new InvalidArgumentException(
        'value must be null or a DateTime object'
      );
    }

    $this->date_disabled = $value;
  }

  public function setEmail($value) {
    if (!(is_null($value) || is_string($value))) {
      throw new InvalidArgumentException(
        'value must be null or a string'
      );
    }

    if (is_string($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
      throw new UnexpectedValueException(
        'value must be a valid email address'
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

  public function setInternalNotes(string $value) {
    if (!is_string($value)) {
      throw new InvalidArgumentException('value must be a string');
    }

    if (strlen($value) > self::MAX_INTERNAL_NOTES) {
      throw new LengthException(sprintf(
        'value must be less than or equal to %d characters',
        self::MAX_INTERNAL_NOTES
      ));
    }

    $this->internal_notes = $value;
  }

  public function setInvitesAvailable(int $value) {
    if ($value < 0 || $value > self::MAX_INVITES_AVAILABLE) {
      throw new InvalidArgumentException(sprintf(
        'value must be an integer in the 0-%d range',
        self::MAX_INVITES_AVAILABLE
      ));
    }

    $this->invites_available = $value;
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
      $this->options |= $option;
    } else {
      $this->options &= ~$option;
    }
  }

  public function setOptions(int $value) {
    if (!is_int($value) || $value < 0) {
      throw new InvalidArgumentException('value must be a positive integer');
    }

    $this->options = $value;
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

  public function setRecordUpdated(DateTime $value) {
    if (!$value instanceof DateTime) {
      throw new InvalidArgumentException(
        'value must be a DateTime object'
      );
    }

    $this->record_updated = $value;
  }

  public function setTimezone(string $value) {
    if (!is_string($value)) {
      throw new InvalidArgumentException('value must be a string');
    }

    if (is_string($value)
      && (empty($value) || strlen($value) > self::MAX_TIMEZONE)) {
      throw new LengthException(sprintf(
        'value must be between 1 and %d characters', self::MAX_TIMEZONE
      ));
    }

    try {
      $tz = new DateTimeZone($value);
      if (!$tz) throw new RuntimeException();
      unset($tz);
    } catch (Exception $e) {
      throw new UnexpectedValueException('value must be a valid timezone');
    }

    $this->timezone = $value;
  }

}
