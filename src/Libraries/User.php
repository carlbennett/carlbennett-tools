<?php

namespace CarlBennett\Tools\Libraries;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\Tools\Libraries\DateTimeImmutable;
use \CarlBennett\Tools\Libraries\User\Acl;
use \CarlBennett\Tools\Libraries\User\Invite as Invitation;
use \DateTimeInterface;
use \DateTimeZone;
use \Ramsey\Uuid\Uuid;
use \StdClass;
use \Throwable;
use \UnexpectedValueException;

class User implements \CarlBennett\Tools\Interfaces\DatabaseObject, \JsonSerializable
{
  public const DEFAULT_OPTION   = 0x00000000;
  public const DEFAULT_TIMEZONE = 'Etc/UTC';

  # Maximum SQL field lengths, alter as appropriate.
  public const MAX_BIOGRAPHY         = 0xFFFF;
  public const MAX_DISPLAY_NAME      = 0xFF;
  public const MAX_EMAIL             = 0xFF;
  public const MAX_INTERNAL_NOTES    = 0xFFFF;
  public const MAX_INVITES_AVAILABLE = 0xFFFF;
  public const MAX_OPTIONS           = 0xFFFFFFFFFFFFFFFF;
  public const MAX_TIMEZONE          = 0xFF;

  public const OPTION_DISABLED = 0x00000001;
  public const OPTION_BANNED   = 0x00000002;

  public const PASSWORD_CHECK_VERIFIED = 1;
  public const PASSWORD_CHECK_UPGRADE  = 2;

  protected string $biography;
  protected DateTimeInterface $date_added;
  protected ?DateTimeInterface $date_banned;
  protected ?DateTimeInterface $date_disabled;
  protected string $display_name;
  protected string $email;
  protected ?string $id;
  protected string $internal_notes;
  protected int $invites_available;
  protected int $options;
  protected string $password_hash;
  protected DateTimeInterface $record_updated;
  protected string $timezone;

  public function __construct(StdClass|string|null $value)
  {
    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
      return;
    }

    $this->setId($value);
    if (!$this->allocate()) throw new UnexpectedValueException();
  }

  public function allocate() : bool
  {
    $now = new DateTimeImmutable('now');

    $this->setBiography('');
    $this->setDateAdded($now);
    $this->setDateBanned(null);
    $this->setDateDisabled(null);
    $this->setName('');
    $this->setEmail('');
    $this->setInternalNotes('');
    $this->setInvitesAvailable(0);
    $this->setOptions(self::DEFAULT_OPTION);
    $this->setPasswordHash('');
    $this->setRecordUpdated($now);
    $this->setTimezone(self::DEFAULT_TIMEZONE);

    $id = $this->getId();
    if (is_null($id)) return true;

    if (!isset(Common::$database)) Common::$database = DatabaseDriver::getDatabaseObject();
    $q = Common::$database->prepare('
      SELECT
        `biography`, `date_added`, `date_banned`, `date_disabled`,
        `display_name`, `email`, UuidFromBin(`id`) AS `id`, `internal_notes`,
        `invites_available`, `options`, `password_hash`, `record_updated`,
        `timezone`
      FROM `users` WHERE `id` = UuidToBin(?) LIMIT 1;
    ');
    if (!$q || !$q->execute([$id])) return false;
    $this->allocateObject($q->fetchObject());
    $q->closeCursor();
    return true;
  }

  protected function allocateObject(StdClass $value) : void
  {
    $this->setBiography($value->biography);
    $this->setDateAdded($value->date_added);
    $this->setDateBanned($value->date_banned);
    $this->setDateDisabled($value->date_disabled);
    $this->setEmail($value->email);
    $this->setId($value->id);
    $this->setInternalNotes($value->internal_notes);
    $this->setInvitesAvailable($value->invites_available);
    $this->setName($value->display_name);
    $this->setOptions($value->options);
    $this->setPasswordHash($value->password_hash);
    $this->setRecordUpdated($value->record_updated);
    $this->setTimezone($value->timezone);
  }

  public function checkPassword(string $password) : int
  {
    $cost = Common::$config->users->crypt_cost;
    $hash = $this->getPasswordHash();
    $rehash = password_needs_rehash(
      $hash, PASSWORD_BCRYPT, array('cost' => $cost)
    );
    $verified = password_verify($password, $hash);

    $r = 0;

    if ($verified) $r |= self::PASSWORD_CHECK_VERIFIED;
    if ($rehash)   $r |= self::PASSWORD_CHECK_UPGRADE;

    return $r;
  }

  public function commit() : bool
  {
    $id = $this->getId();
    if (is_null($id)) $id = Uuid::uuid4();

    if (!isset(Common::$database)) Common::$database = DatabaseDriver::getDatabaseObject();
    $q = Common::$database->prepare('
      INSERT INTO `users` (
        `biography`, `date_added`, `date_banned`, `date_disabled`,
        `display_name`, `email`, `id`, `internal_notes`, `invites_available`,
        `options`, `password_hash`, `record_updated`, `timezone`
      ) VALUES (
        :bio, :added, :banned, :disabled, :name, :email, UuidToBin(:id),
        :int_notes, :invites_a, :options, :password, :record_updated, :tz
      ) ON DUPLICATE KEY UPDATE
        `biography` = :bio, `date_added` = :added, `date_banned` = :banned,
        `date_disabled` = :disabled, `display_name` = :name, `email` = :email,
        `internal_notes` = :int_notes, `invites_available` = :invites_a,
        `options` = :options, `password_hash` = :password,
        `record_updated` = :record_updated, `timezone` = :tz
      ;
    ');

    $p = [
      ':added' => $this->getDateAdded(),
      ':banned' => $this->getDateBanned(),
      ':bio' => $this->getBiography(),
      ':disabled' => $this->getDateDisabled(),
      ':email' => $this->getEmail(),
      ':id' => $id,
      ':int_notes' => $this->getInternalNotes(),
      ':invites_a' => $this->getInvitesAvailable(),
      ':name' => $this->getName(),
      ':options' => $this->getOptions(),
      ':password' => $this->getPasswordHash(),
      ':record_updated' => $this->getRecordUpdated(),
      ':tz' => $this->getTimezone(),
    ];

    foreach ($p as $k => $v)
      if ($v instanceof DateTimeInterface)
        $p[$k] = $v->format(self::DATE_SQL);

    if (!$q || !$q->execute($p)) return false;
    $q->closeCursor();
    return true;
  }

  public static function createPassword(string $password) : string
  {
    if (empty($password)) throw new UnexpectedValueException('value must not be empty');
    $cost = Common::$config->users->crypt_cost;
    return password_hash($password, PASSWORD_BCRYPT, array('cost' => $cost));
  }

  public function deallocate() : bool
  {
    $id = $this->getId();
    if (is_null($id)) return false;
    if (!isset(Common::$database)) Common::$database = DatabaseDriver::getDatabaseObject();
    $q = Common::$database->prepare('DELETE FROM `users` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { if ($q) $q->closeCursor(); }
  }

  public static function getAll() : ?array
  {
    if (!isset(Common::$database)) Common::$database = DatabaseDriver::getDatabaseObject();
    $q = Common::$database->prepare('
      SELECT
        `biography`, `date_added`, `date_banned`, `date_disabled`,
        `display_name`, `email`, UuidFromBin(`id`) AS `id`, `internal_notes`,
        `invites_available`, `options`, `password_hash`, `record_updated`,
        `timezone`
      FROM `users`
      ORDER BY `date_added`, `display_name`, `email`;
    ');
    if (!$q || !$q->execute()) return null;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new self($row);
    $q->closeCursor();
    return $r;
  }

  public static function getByEmail(string $value) : self|bool
  {
    if (!isset(Common::$database)) Common::$database = DatabaseDriver::getDatabaseObject();
    $q = Common::$database->prepare('
      SELECT
        `biography`, `date_added`, `date_banned`, `date_disabled`,
        `display_name`, `email`, UuidFromBin(`id`) AS `id`, `internal_notes`,
        `invites_available`, `options`, `password_hash`, `record_updated`,
        `timezone`
      FROM `users` WHERE `email` = ?;
    ');
    if (!$q || !$q->execute([$value]) || $q->rowCount() != 1) return false;
    $r = new self($q->fetchObject());
    $q->closeCursor();
    return $r;
  }

  public function getAclObject() : Acl
  {
    return new Acl($this->id);
  }

  public function getBiography() : string
  {
    return $this->biography;
  }

  public function getDateAdded() : DateTimeInterface
  {
    return $this->date_added;
  }

  public function getDateBanned() : ?DateTimeInterface
  {
    return $this->date_banned;
  }

  public function getDateDisabled() : ?DateTimeInterface
  {
    return $this->date_disabled;
  }

  public function getEmail() : string
  {
    return $this->email;
  }

  public function getId() : ?string
  {
    return $this->id;
  }

  public function getInternalNotes() : string
  {
    return $this->internal_notes;
  }

  public function getInvitesAvailable() : int
  {
    return $this->invites_available;
  }

  public function getInvitesSent() : ?array
  {
    $id = $this->getId();
    if (is_null($id)) throw new UnexpectedValueException('id must be set prior to call');
    if (!isset(Common::$database)) Common::$database = DatabaseDriver::getDatabaseObject();
    $q = Common::$database->prepare(
      'SELECT UuidFromBin(`id`) AS `id` FROM `user_invites` WHERE `invited_by` = UuidToBin(?);'
    );
    if (!$q || !$q->execute([$id])) return null;
    $r = [];
    while ($row = $q->fetchObject()) $r[] = new Invitation($row->id);
    $q->closeCursor();
    return $r;
  }

  public function getInvitesUsed() : ?int
  {
    $id = $this->getId();
    if (is_null($id)) throw new UnexpectedValueException('id must be set prior to call');
    if (!isset(Common::$database)) Common::$database = DatabaseDriver::getDatabaseObject();
    $q = Common::$database->prepare(
      'SELECT COUNT(*) AS `count` FROM `user_invites` WHERE `invited_by` = UuidToBin(?);'
    );
    if (!$q || !$q->execute([$id])) return null;
    $r = $q->fetchObject()->count;
    $q->closeCursor();
    return $r;
  }

  public function getName() : string
  {
    return $this->display_name;
  }

  public function getOption(int $option) : bool
  {
    return ($this->options & $option) === $option;
  }

  public function getOptions() : int
  {
    return $this->options;
  }

  public function getPasswordHash() : string
  {
    return $this->password_hash;
  }

  public function getRecordUpdated() : DateTimeInterface
  {
    return $this->record_updated;
  }

  public function getTimezone() : string
  {
    return $this->timezone;
  }

  public function getTimezoneObject() : ?DateTimeZone
  {
    return empty($this->timezone) ? null : new DateTimeZone($this->timezone);
  }

  public function getUrl(string $subcontroller = '') : string
  {
    return Common::relativeUrlToAbsolute(sprintf(
      '/user/%s%s', (
        empty($subcontroller) ? '' : $subcontroller . '/'
      ), $this->getId()
    ));
  }

  public function isBanned() : bool
  {
    return $this->getOption(self::OPTION_BANNED);
  }

  public function isDisabled() : bool
  {
    return $this->getOption(self::OPTION_DISABLED);
  }

  public function jsonSerialize() : mixed
  {
    return [
      'biography' => $this->getBiography(),
      'date_added' => $this->getDateAdded(),
      'date_banned' => $this->getDateBanned(),
      'date_disabled' => $this->getDateDisabled(),
      'email' => $this->getEmail(),
      'id' => $this->getId(),
      'invites_available' => $this->getInvitesAvailable(),
      'name' => $this->getName(),
      'notes' => $this->getInternalNotes(),
      'options' => $this->getOptions(),
      'record_updated' => $this->getRecordUpdated(),
      'timezone' => $this->getTimezone(),
    ];
  }

  public function setBiography(string $value) : void
  {
    if (strlen($value) > self::MAX_BIOGRAPHY)
      throw new UnexpectedValueException(sprintf(
        'value must be between 0-%d characters', self::MAX_BIOGRAPHY
      ));

    $this->biography = $value;
  }

  public function setDateAdded(DateTimeInterface|string $value) : void
  {
    $this->date_added = (is_string($value) ? new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value);
  }

  public function setDateBanned(DateTimeInterface|string|null $value) : void
  {
    $this->date_banned = (is_string($value) ? new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value);
  }

  public function setDateDisabled(DateTimeInterface|string|null $value) : void
  {
    $this->date_disabled = (is_string($value) ? new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value);
  }

  public function setEmail(?string $value) : void
  {
    if (is_string($value) && strlen($value) > self::MAX_EMAIL)
      throw new UnexpectedValueException(sprintf(
        'value must be between 0-%d characters', self::MAX_EMAIL
      ));

    if (is_string($value) && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL))
      throw new UnexpectedValueException('value must be a valid email address');

    $this->email = $value;
  }

  public function setId(?string $value) : void
  {
    if (!(is_null($value) || (is_string($value) && preg_match(self::UUID_REGEX, $value) === 1)))
      throw new UnexpectedValueException('value must be null or a string in UUID format');

    $this->id = $value;
  }

  public function setInternalNotes(string $value) : void
  {
    if (strlen($value) > self::MAX_INTERNAL_NOTES)
      throw new UnexpectedValueException(sprintf(
        'value must be between 0-%d characters', self::MAX_INTERNAL_NOTES
      ));

    $this->internal_notes = $value;
  }

  public function setInvitesAvailable(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_INVITES_AVAILABLE)
      throw new UnexpectedValueException(sprintf(
        'value must be an integer between range 0-%d', self::MAX_INVITES_AVAILABLE
      ));

    $this->invites_available = $value;
  }

  public function setName(string $value) : void
  {
    if (strlen($value) > self::MAX_DISPLAY_NAME)
      throw new UnexpectedValueException(sprintf(
        'value must be between 0-%d characters', self::MAX_DISPLAY_NAME
      ));

    $this->display_name = $value;
  }

  public function setOption(int $option, bool $value) : void
  {
    if ($value) $this->options |= $option;
    else $this->options &= ~$option;
  }

  public function setOptions(int $value) : void
  {
    if ($value < 0 || $value > self::MAX_OPTIONS)
      throw new UnexpectedValueException(sprintf('value must be an integer between range 0-%d', self::MAX_OPTIONS));

    $this->options = $value;
  }

  public function setPasswordHash(string $value) : void
  {
    $this->password_hash = $value;
  }

  public function setRecordUpdated(DateTimeInterface|string $value) : void
  {
    $this->record_updated = (is_string($value) ? new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value);
  }

  public function setTimezone(DateTimeZone|string $value) : void
  {
    if (is_string($value) && strlen($value) > self::MAX_TIMEZONE)
      throw new UnexpectedValueException(sprintf(
        'value must be between 0-%d characters', self::MAX_TIMEZONE
      ));

    try
    {
      if (is_string($value) && !empty($value)) new DateTimeZone($value);
    }
    catch (Throwable $e)
    {
      throw new UnexpectedValueException('value must be a valid timezone', 0, $e);
    }

    $this->timezone = is_string($value) ? $value : $value->getName();
  }
}
