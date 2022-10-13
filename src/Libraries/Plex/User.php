<?php

namespace CarlBennett\Tools\Libraries\Plex;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\DateTime;
use \CarlBennett\MVC\Libraries\Gravatar;
use \CarlBennett\Tools\Libraries\IDatabaseObject;
use \CarlBennett\Tools\Libraries\User as BaseUser;

use \DateTimeZone;
use \InvalidArgumentException;
use \JsonSerializable;
use \LengthException;
use \PDO;
use \PDOException;
use \StdClass;
use \UnexpectedValueException;

class User implements IDatabaseObject, JsonSerializable {

  const DATE_SQL = 'Y-m-d H:i:s';

  # Maximum SQL field lengths, alter as appropriate.
  const MAX_NOTES         = 65535;
  const MAX_PLEX_EMAIL    = 255;
  const MAX_PLEX_THUMB    = 255;
  const MAX_PLEX_TITLE    = 255;
  const MAX_PLEX_USERNAME = 255;

  const OPTION_DEFAULT  = 0x00000000;
  const OPTION_DISABLED = 0x00000001;
  const OPTION_HIDDEN   = 0x00000002;
  const OPTION_HOMEUSER = 0x00000004;

  const RISK_UNASSESSED = 0;
  const RISK_LOW        = 1;
  const RISK_MEDIUM     = 2;
  const RISK_HIGH       = 3;

  const UUID_REGEX = '/([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}/';

  private $_id;

  protected $date_added;
  protected $date_disabled;
  protected $date_expired;
  protected $id;
  protected $notes;
  protected $options;
  protected $plex_email;
  protected $plex_id;
  protected $plex_thumb;
  protected $plex_title;
  protected $plex_username;
  protected $record_updated;
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

    $now = new DateTime('now');
    $this->setDateAdded($now);
    $this->setNotes('');
    $this->setOptions(self::OPTION_DEFAULT);
    $this->setRecordUpdated($now);
    $this->setRisk(self::RISK_UNASSESSED);

    if (empty($id)) return;

    $this->setId($id);

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare('
      SELECT `date_added`, `date_disabled`, `date_expired`,
             UuidFromBin(`id`) AS `id`, `notes`, `options`, `plex_email`,
             `plex_id`, `plex_thumb`, `plex_title`, `plex_username`,
             `record_updated`, `risk`, UuidFromBin(`user_id`) AS `user_id`
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

    $this->setDateAdded(new DateTime($value->date_added, $tz));
    $this->setDateDisabled(
      $value->date_disabled ? new DateTime($value->date_disabled, $tz) : null
    );
    $this->setDateExpired(
      $value->date_expired ? new DateTime($value->date_expired, $tz) : null
    );
    $this->setId($value->id);
    $this->setNotes($value->notes);
    $this->setOptions($value->options);
    $this->setPlexEmail($value->plex_email);
    $this->setPlexId($value->plex_id);
    $this->setPlexThumb($value->plex_thumb);
    $this->setPlexTitle($value->plex_title);
    $this->setPlexUsername($value->plex_username);
    $this->setRecordUpdated(new DateTime($value->record_updated, $tz));
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
        `date_added`, `date_disabled`, `date_expired`, `id`, `notes`, `options`,
        `plex_email`, `plex_id`, `plex_thumb`, `plex_title`, `plex_username`,
        `record_updated`, `risk`, `user_id`
      ) VALUES (
        :added, :disabled, :expired, UuidToBin(:id), :notes, :options,
        :plex_email, :plex_id, :plex_thumb, :plex_title, :plex_username,
        :record_updated, :risk, UuidToBin(:user_id)
      ) ON DUPLICATE KEY UPDATE
        `date_added` = :added, `date_disabled` = :disabled,
        `date_expired` = :expired, `notes` = :notes, `options` = :options,
        `plex_email` = :plex_email, `plex_id` = :plex_id,
        `plex_thumb` = :plex_thumb, `plex_title` = :plex_title,
        `plex_username` = :plex_username,
        `record_updated` = :record_updated, `risk` = :risk,
        `user_id` = UuidToBin(:user_id)
      ;
    ');

    $date_added = $this->date_added->format(self::DATE_SQL);

    $date_disabled = (
      is_null($this->date_disabled) ?
      null : $this->date_disabled->format(self::DATE_SQL)
    );

    $date_expired = (
      is_null($this->date_expired) ?
      null : $this->date_expired->format(self::DATE_SQL)
    );

    $record_updated = $this->record_updated->format(self::DATE_SQL);

    $q->bindParam(':added', $date_added, PDO::PARAM_STR);

    $q->bindParam(':disabled', $date_disabled, (
      is_null($date_disabled) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':expired', $date_expired, (
      is_null($date_expired) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':id', $this->id, PDO::PARAM_STR);
    $q->bindParam(':notes', $this->notes, PDO::PARAM_STR);
    $q->bindParam(':options', $this->options, PDO::PARAM_INT);

    $q->bindParam(':plex_email', $this->plex_email, (
      is_null($this->plex_email) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':plex_id', $this->plex_id, (
      is_null($this->plex_id) ? PDO::PARAM_NULL : PDO::PARAM_INT
    ));

    $q->bindParam(':plex_thumb', $this->plex_thumb, (
      is_null($this->plex_thumb) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':plex_title', $this->plex_title, (
      is_null($this->plex_title) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':plex_username', $this->plex_username, (
      is_null($this->plex_username) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':record_updated', $record_updated, PDO::PARAM_STR);
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
      SELECT `date_added`, `date_disabled`, `date_expired`,
             UuidFromBin(`id`) AS `id`, `notes`, `options`, `plex_email`,
             `plex_id`, `plex_thumb`, `plex_title`, `plex_username`,
             `record_updated`, `risk`, UuidFromBin(`user_id`) AS `user_id`
      FROM `plex_users`
      ORDER BY `date_added`, `plex_title`, `plex_username`, `plex_email`;
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

  /**
   * Gets the avatar thumbnail url for this Plex user.
   *
   * @param integer|null $size The Size parameter to pass to the Gravatar service, ignored if the property $plex_thumb is non-empty.
   * @return string The $plex_thumb property if non-empty, otherwise the Gravatar url based on email.
   */
  public function getAvatar(?int $size = null) : string
  {
    if (!empty($this->plex_thumb)) return $this->plex_thumb;

    $email = $this->getPlexEmail() ?? '';
    if (empty($email))
    {
      $user = $this->getUser();
      if ($user) $email = $user->getEmail() ?? '';
    }

    if (empty($email)) $email = 'nobody@example.com'; // no email is set??

    return (new Gravatar($email))->getUrl($size, 'mp');
  }

  public function getDateAdded() {
    return $this->date_added;
  }

  public function getDateDisabled() {
    return $this->date_disabled;
  }

  public function getDateExpired() {
    return $this->date_expired;
  }

  public function getId() {
    return $this->id;
  }

  public function getNotes() {
    return $this->notes;
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

  public function getPlexEmail() {
    return $this->plex_email;
  }

  public function getPlexId() {
    return $this->plex_id;
  }

  public function getPlexThumb() {
    return $this->plex_thumb;
  }

  public function getPlexTitle() {
    return $this->plex_title;
  }

  public function getPlexUsername() {
    return $this->plex_username;
  }

  public function getRecordUpdated() {
    return $this->record_updated;
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

  public function isDisabled() {
    return $this->getOption(self::OPTION_DISABLED);
  }

  public function isExpired() {
    return !is_null($this->getDateExpired());
  }

  public function isHidden() {
    return $this->getOption(self::OPTION_HIDDEN);
  }

  public function isHighRisk() {
    return $this->risk == self::RISK_HIGH;
  }

  public function isHomeUser() {
    return $this->getOption(self::OPTION_HOMEUSER);
  }

  public function isMediumRisk() {
    return $this->risk == self::RISK_MEDIUM;
  }

  public function isLowRisk() {
    return $this->risk == self::RISK_LOW;
  }

  public function isUnassessedRisk() {
    return $this->risk == self::RISK_UNASSESSED;
  }

  public function jsonSerialize() : array
  {
    return [
      'date_added' => $this->date_added,
      'date_disabled' => $this->date_disabled,
      'date_expired' => $this->date_expired,
      'id' => $this->id,
      'notes' => $this->notes,
      'options' => $this->options,
      'plex_email' => $this->plex_email,
      'plex_id' => $this->plex_id,
      'plex_thumb' => $this->plex_thumb,
      'plex_title' => $this->plex_title,
      'plex_username' => $this->plex_username,
      'record_updated' => $this->record_updated,
      'risk' => $this->risk,
      'user_id' => $this->user_id,
    ];
  }

  public function setDateAdded(DateTime $value) {
    if (!$value instanceof DateTime) {
      throw new InvalidArgumentException(
        'value must be a DateTime object'
      );
    }

    $this->date_added = $value;
  }

  public function setDateDisabled($value) {
    if (!(is_null($value) || $value instanceof DateTime)) {
      throw new InvalidArgumentException(
        'value must be null or a DateTime object'
      );
    }

    $this->date_disabled = $value;
  }

  public function setDateExpired($value) {
    if (!(is_null($value) || $value instanceof DateTime)) {
      throw new InvalidArgumentException(
        'value must be null or a DateTime object'
      );
    }

    $this->date_expired = $value;
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

  public function setPlexId(?int $value)
  {
    $this->plex_id = $value;
  }

  public function setPlexThumb($value, $auto_null = true) {
    if (!(is_null($value) || is_string($value))) {
      throw new InvalidArgumentException(
        'value must be null or a string'
      );
    }

    if ($auto_null && is_string($value) && empty($value)) { $value = null; }

    if (is_string($value) && strlen($value) > self::MAX_PLEX_THUMB) {
      throw new LengthException(sprintf(
        'value must be less than or equal to %d characters',
        self::MAX_PLEX_THUMB
      ));
    }

    $this->plex_thumb = $value;
  }

  public function setPlexTitle($value, $auto_null = true) {
    if (!(is_null($value) || is_string($value))) {
      throw new InvalidArgumentException(
        'value must be null or a string'
      );
    }

    if ($auto_null && is_string($value) && empty($value)) { $value = null; }

    if (is_string($value) && strlen($value) > self::MAX_PLEX_TITLE) {
      throw new LengthException(sprintf(
        'value must be less than or equal to %d characters',
        self::MAX_PLEX_TITLE
      ));
    }

    $this->plex_title = $value;
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

  public function setRecordUpdated(DateTime $value) {
    if (!$value instanceof DateTime) {
      throw new InvalidArgumentException(
        'value must be a DateTime object'
      );
    }

    $this->record_updated = $value;
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
