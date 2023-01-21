<?php
declare(strict_types=1);
namespace CarlBennett\Tools\Libraries\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\Tools\Libraries\Database;
use \CarlBennett\Tools\Libraries\DateTimeImmutable;
use \CarlBennett\Tools\Libraries\User\User;
use \DateTimeInterface;
use \DateTimeZone;
use \PDO;
use \StdClass;
use \UnexpectedValueException;

class Invite implements \CarlBennett\Tools\Interfaces\DatabaseObject, \JsonSerializable
{
  # Maximum SQL field lengths, alter as appropriate.
  public const MAX_EMAIL = 0xFF;

  protected ?DateTimeImmutable $date_accepted;
  protected DateTimeImmutable $date_invited;
  protected ?DateTimeImmutable $date_revoked;
  protected string $email;
  protected ?string $id;
  protected ?string $invited_by;
  protected ?string $invited_user;
  protected DateTimeImmutable $record_updated;

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
    $this->setDateAccepted(null);
    $this->setDateInvited('now');
    $this->setDateRevoked(null);
    $this->setEmail('', true);
    $this->setInvitedBy(null);
    $this->setInvitedUser(null);
    $this->setRecordUpdated('now');

    $id = $this->getId();
    if (is_null($id)) return true;

    $q = Database::instance()->prepare('
      SELECT
        `date_accepted`, `date_invited`, `date_revoked`, `email`,
        UuidFromBin(`id`) AS `id`, UuidFromBin(`invited_by`) AS `invited_by`,
        UuidFromBin(`invited_user`) AS `invited_user`, `record_updated`
      FROM `user_invites` WHERE id = UuidToBin(?) LIMIT 1;
    ');
    if (!$q || !$q->execute([$id]) || $q->rowCount() != 1) return false;
    $this->allocateObject($q->fetchObject());
    $q->closeCursor();
    return true;
  }

  protected function allocateObject(StdClass $value) : void
  {
    $this->setDateAccepted($value->date_accepted);
    $this->setDateInvited($value->date_invited);
    $this->setDateRevoked($value->date_revoked);
    $this->setEmail($value->email);
    $this->setId($value->id);
    $this->setInvitedBy($value->invited_by);
    $this->setInvitedUser($value->invited_user);
    $this->setRecordUpdated($value->record_updated);
  }

  public function commit() : bool
  {
    $q = Database::instance()->prepare(
      'INSERT INTO `user_invites` (
        `date_accepted`, `date_invited`, `date_revoked`, `email`,
        `id`, `invited_by`, `invited_user`, `record_updated`
      ) VALUES (
        :accepted, :invited, :revoked, :email, UuidToBin(:id),
        UuidToBin(:invited_by), UuidToBin(:invited_user), :record_updated
      ) ON DUPLICATE KEY UPDATE
        `date_accepted` = :accepted, `date_invited` = :invited,
        `date_revoked` = :revoked, `email` = :email,
        `invited_by` = UuidToBin(:invited_by),
        `invited_user` = UuidToBin(:invited_user),
        `record_updated` = :record_updated
      ;'
    );

    $p = [
      ':accepted' => $this->getDateAccepted(),
      ':email' => $this->getEmail(),
      ':id' => $this->getId(),
      ':invited_by' => $this->getInvitedBy(),
      ':invited_user' => $this->getInvitedUser(),
      ':invited' => $this->getDateInvited(),
      ':record_updated', $this->getRecordUpdated(),
      ':revoked' => $this->getDateRevoked(),
    ];
    if (!$q || !$q->execute($p)) return false;
    $q->closeCursor();
    return true;
  }
 
  public function deallocate() : bool
  {
    $id = $this->getId();
    if (is_null($id)) return false;
    $q = Database::instance()->prepare('DELETE FROM `user_invites` WHERE `id` = ? LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { if ($q) $q->closeCursor(); }
  }

  /**
   * @param string $value The email address to lookup the invite for.
   * @return ?Invite The invite object or null if not found.
   */
  public static function getByEmail(string $value)
  {
    $q = Database::instance()->prepare(
      'SELECT UuidFromBin(`id`) AS `id` FROM `user_invites`
      WHERE `email` = :email LIMIT 1;'
    );

    $q->bindParam(':email', $value, PDO::PARAM_STR);

    $r = $q->execute();
    if (!$r) return null;
    if ($q->rowCount() === 0) return null;

    $id = $q->fetch(PDO::FETCH_NUM)[0];
    $q->closeCursor();

    return new self($id);
  }

  public function getDateAccepted() : ?DateTimeInterface
  {
    return $this->date_accepted;
  }

  public function getDateInvited() : DateTimeInterface
  {
    return $this->date_invited;
  }

  public function getDateRevoked() : ?DateTimeInterface
  {
    return $this->date_revoked;
  }

  public function getEmail() : string
  {
    return $this->email;
  }

  /**
   * @return string The UUID in hexadecimal string format (with dashes).
   */
  public function getId() : ?string
  {
    return $this->id;
  }

  public function getInvitedBy() : ?User
  {
    return $this->invited_by;
  }

  public function getInvitedUser() : ?User
  {
    return $this->invited_user;
  }

  public function getRecordUpdated() : DateTimeInterface
  {
    return $this->record_updated;
  }

  public function jsonSerialize(): mixed
  {
    return [
      'date_accepted' => $this->getDateAccepted(),
      'date_invited' => $this->getDateInvited(),
      'date_revoked' => $this->getDateRevoked(),
      'email' => $this->getEmail(),
      'id' => $this->getId(),
      'invited_by' => $this->getInvitedBy(),
      'invited_user' => $this->getInvitedUser(),
      'record_updated' => $this->getRecordUpdated(),
    ];
  }

  public function setDateAccepted(DateTimeInterface|string|null $value) : void
  {
    $this->date_accepted = (\is_null($value) ? null : (\is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : DateTimeImmutable::createFromInterface($value)
    ));
  }

  public function setDateInvited(DateTimeInterface|string $value) : void
  {
    $this->date_invited = (\is_null($value) ? null : (\is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : DateTimeImmutable::createFromInterface($value)
    ));
  }

  public function setDateRevoked(DateTimeInterface|string|null $value) : void
  {
    $this->date_revoked = (\is_null($value) ? null : (\is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : DateTimeImmutable::createFromInterface($value)
    ));
  }

  /**
   * @param string $value The valid email address string to set.
   * @param bool $force If true, skips check with filter_var(). Default: false
   */
  public function setEmail(string $value, bool $force = false) : void
  {
    if (strlen($value) > self::MAX_EMAIL)
    {
      // UnexpectedValueException is at Runtime
      throw new UnexpectedValueException(sprintf(
        'value string length must be less than or equal to %d', self::MAX_EMAIL
      ));
    }

    if (!$force && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL))
    {
      // UnexpectedValueException is at Runtime
      throw new UnexpectedValueException('value is not a valid email address');
    }

    $this->email = $value;
  }

  /**
   * @param string|null $value The UUID in hexadecimal format (with dashes) to set.
   *                           Example: "31952e1c-d05a-44a0-b749-6d892cc96d3a"
   */
  public function setId(?string $value) : void
  {
    if (!(is_null($value) || (is_string($value) && preg_match(self::UUID_REGEX, $value) === 1)))
      throw new UnexpectedValueException('value must be null or a string in UUID format');

    $this->id = $value;
  }

  public function setInvitedBy(User|string|null $value) : void
  {
    if (is_string($value) && preg_match(self::UUID_REGEX, $value) !== 1)
      throw new UnexpectedValueException('value must be null, User, or a string in UUID format');

    $this->invited_by = $value instanceof User ? $value->getId() : $value;
  }

  public function setInvitedUser(User|string|null $value) : void
  {
    if (is_string($value) && preg_match(self::UUID_REGEX, $value) !== 1)
      throw new UnexpectedValueException('value must be null, User, or a string in UUID format');

    $this->invited_user = $value instanceof User ? $value->getId() : $value;
  }

  public function setRecordUpdated(DateTimeInterface|string $value) : void
  {
    $this->record_updated = (\is_null($value) ? null : (\is_string($value) ?
      new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : DateTimeImmutable::createFromInterface($value)
    ));
  }
}
