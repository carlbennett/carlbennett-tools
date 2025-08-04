<?php

namespace CarlBennett\Tools\Libraries\Plex;

use \CarlBennett\Tools\Libraries\Core\DateTimeImmutable;
use \CarlBennett\Tools\Libraries\Db\MariaDb;
use \CarlBennett\Tools\Libraries\User\User as BaseUser;
use \DateTimeInterface;
use \DateTimeZone;
use \Ramsey\Uuid\Uuid;
use \StdClass;
use UnexpectedValueException;

class User implements \CarlBennett\Tools\Interfaces\DatabaseObject, \JsonSerializable
{
  # Maximum SQL field lengths, alter as appropriate.
  public const MAX_NOTES         = 0xFFFF;
  public const MAX_OPTIONS       = 0xFFFFFFFFFFFFFFFF;
  public const MAX_PLEX_EMAIL    = 0xFF;
  public const MAX_PLEX_ID       = 0xFFFFFFFFFFFFFFFF;
  public const MAX_PLEX_THUMB    = 0xFF;
  public const MAX_PLEX_TITLE    = 0xFF;
  public const MAX_PLEX_USERNAME = 0xFF;

  public const OPTION_DEFAULT  = 0x0000000000000000;
  public const OPTION_DISABLED = 0x0000000000000001;
  public const OPTION_HIDDEN   = 0x0000000000000002;
  public const OPTION_HOMEUSER = 0x0000000000000004;

  public const RISK_UNASSESSED = 0;
  public const RISK_LOW        = 1;
  public const RISK_MEDIUM     = 2;
  public const RISK_HIGH       = 3;

  protected DateTimeInterface $date_added;
  protected ?DateTimeInterface $date_disabled;
  protected ?DateTimeInterface $date_expired;
  protected ?string $id;
  protected string $notes;
  protected int $options;
  protected ?string $plex_email;
  protected ?string $plex_id;
  protected ?string $plex_thumb;
  protected ?string $plex_title;
  protected ?string $plex_username;
  protected DateTimeInterface $record_updated;
  protected int $risk;
  protected ?string $user_id;

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

  public function allocate(): bool
  {
    $now = new DateTimeImmutable('now');

    $this->setDateAdded($now);
    $this->setDateDisabled(null);
    $this->setDateExpired(null);
    $this->setNotes('');
    $this->setOptions(self::OPTION_DEFAULT);
    $this->setPlexEmail(null);
    $this->setPlexId(null);
    $this->setPlexThumb(null);
    $this->setPlexTitle(null);
    $this->setPlexUsername(null);
    $this->setRecordUpdated($now);
    $this->setRisk(self::RISK_UNASSESSED);
    $this->setUserId(null);

    $id = $this->getId();
    if (is_null($id)) return true;

    try
    {
      $q = MariaDb::instance()->prepare('
        SELECT `date_added`, `date_disabled`, `date_expired`,
              UuidFromBin(`id`) AS `id`, `notes`, `options`, `plex_email`,
              `plex_id`, `plex_thumb`, `plex_title`, `plex_username`,
              `record_updated`, `risk`, UuidFromBin(`user_id`) AS `user_id`
        FROM `plex_users` WHERE `id` = UuidToBin(?) LIMIT 1;
      ');
      if (!$q || !$q->execute([$id]) || $q->rowCount() != 1) return false;
      $this->allocateObject($q->fetchObject());
      return true;
    }
    finally { if ($q) $q->closeCursor(); }
  }

  private function allocateObject(StdClass $value): void
  {
    $this->setDateAdded($value->date_added);
    $this->setDateDisabled($value->date_disabled);
    $this->setDateExpired($value->date_expired);
    $this->setId($value->id);
    $this->setNotes($value->notes);
    $this->setOptions($value->options);
    $this->setPlexEmail($value->plex_email);
    $this->setPlexId($value->plex_id);
    $this->setPlexThumb($value->plex_thumb);
    $this->setPlexTitle($value->plex_title);
    $this->setPlexUsername($value->plex_username);
    $this->setRecordUpdated($value->record_updated);
    $this->setRisk($value->risk);
    $this->setUserId($value->user_id);
  }

  public function commit(): bool
  {
    $id = $this->getId();
    if (is_null($id)) $id = Uuid::uuid4();

    try
    {
      $q = MariaDb::instance()->prepare('
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

      $p = [
        ':added' => $this->getDateAdded(),
        ':disabled' => $this->getDateDisabled(),
        ':expired' => $this->getDateExpired(),
        ':id' => $id,
        ':notes' => $this->getNotes(),
        ':options' => $this->getOptions(),
        ':plex_email' => $this->getPlexEmail(),
        ':plex_id' => $this->getPlexId(),
        ':plex_thumb' => $this->getPlexThumb(),
        ':plex_title' => $this->getPlexTitle(),
        ':plex_username' => $this->getPlexUsername(),
        ':record_updated' => $this->getRecordUpdated(),
        ':risk' => $this->getRisk(),
        ':user_id' => $this->getUserId(),
      ];

      foreach ($p as $k => $v)
        if ($v instanceof DateTimeInterface)
          $p[$k] = $v->format(self::DATE_SQL);

      if (!$q || !$q->execute($p)) return false;
      $this->setId($id);
      return true;
    }
    finally { if ($q) $q->closeCursor(); }
  }

  public function deallocate(): bool
  {
    $id = $this->getId();
    if (is_null($id)) return false;
    $q = MariaDb::instance()->prepare('DELETE FROM `plex_users` WHERE `id` = UuidToBin(?) LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { if ($q) $q->closeCursor(); }
  }

  public static function getAll(): ?array
  {
    try
    {
      $q = MariaDb::instance()->prepare('
        SELECT `date_added`, `date_disabled`, `date_expired`,
              UuidFromBin(`id`) AS `id`, `notes`, `options`, `plex_email`,
              `plex_id`, `plex_thumb`, `plex_title`, `plex_username`,
              `record_updated`, `risk`, UuidFromBin(`user_id`) AS `user_id`
        FROM `plex_users`
        ORDER BY `date_added`, `plex_title`, `plex_username`, `plex_email`;
      ');
      if (!$q || !$q->execute()) return null;
      $r = [];
      while ($row = $q->fetchObject()) $r[] = new self($row);
      return $r;
    }
    finally { if ($q) $q->closeCursor(); }
  }

  /**
   * Gets the avatar thumbnail url for this Plex user.
   *
   * @param integer|null $size The Size parameter to pass to the Gravatar service, ignored if the property $plex_thumb is non-empty.
   * @return string The $plex_thumb property if non-empty, otherwise the Gravatar url based on email.
   */
  public function getAvatar(?int $size = null): string
  {
    if (!empty($this->plex_thumb)) return $this->plex_thumb;

    $email = $this->getPlexEmail() ?? '';
    if (empty($email))
    {
      $user = $this->getUser();
      if ($user) $email = $user->getEmail() ?? '';
    }

    if (empty($email)) $email = 'nobody@example.com'; // no email is set??

    return (new \CarlBennett\Tools\Libraries\User\Gravatar($email))->getUrl($size, 'mp');
  }

  public function getDateAdded(): DateTimeInterface
  {
    return $this->date_added;
  }

  public function getDateDisabled(): ?DateTimeInterface
  {
    return $this->date_disabled;
  }

  public function getDateExpired(): ?DateTimeInterface
  {
    return $this->date_expired;
  }

  public function getId(): ?string
  {
    return $this->id;
  }

  public function getNotes(): string
  {
    return $this->notes;
  }

  public function getOption(int $option): bool
  {
    return ($this->options & $option) === $option;
  }

  public function getOptions(): int
  {
    return $this->options;
  }

  public function getPlexEmail(): ?string
  {
    return $this->plex_email;
  }

  public function getPlexId(): ?int
  {
    return $this->plex_id;
  }

  public function getPlexThumb(): ?string
  {
    return $this->plex_thumb;
  }

  public function getPlexTitle(): ?string
  {
    return $this->plex_title;
  }

  public function getPlexUsername(): ?string
  {
    return $this->plex_username;
  }

  public function getRecordUpdated(): DateTimeInterface
  {
    return $this->record_updated;
  }

  public function getRisk() : int
  {
    return $this->risk;
  }

  public function getUser(): ?BaseUser
  {
    return is_null($this->user_id) ? null : new BaseUser($this->user_id);
  }

  public function getUserId(): ?string
  {
    return $this->user_id;
  }

  public function isDisabled(): bool
  {
    return $this->getOption(self::OPTION_DISABLED);
  }

  public function isExpired(): bool
  {
    return !is_null($this->getDateExpired());
  }

  public function isHidden(): bool
  {
    return $this->getOption(self::OPTION_HIDDEN);
  }

  public function isHighRisk(): bool
  {
    return $this->risk == self::RISK_HIGH;
  }

  public function isHomeUser(): bool
  {
    return $this->getOption(self::OPTION_HOMEUSER);
  }

  public function isMediumRisk(): bool
  {
    return $this->risk == self::RISK_MEDIUM;
  }

  public function isLowRisk(): bool
  {
    return $this->risk == self::RISK_LOW;
  }

  public function isUnassessedRisk(): bool
  {
    return $this->risk == self::RISK_UNASSESSED;
  }

  public function jsonSerialize(): mixed
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

  public function setDateAdded(DateTimeInterface|string $value): void
  {
    $this->date_added = (is_string($value) ? new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value);
  }

  public function setDateDisabled(DateTimeInterface|string|null $value): void
  {
    $this->date_disabled = (is_string($value) ? new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value);
  }

  public function setDateExpired(DateTimeInterface|string|null $value): void
  {
    $this->date_expired = (is_string($value) ? new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value);
  }

  public function setId(?string $value): void
  {
    if (!(is_null($value) || (is_string($value) && preg_match(self::UUID_REGEX, $value) === 1)))
      throw new UnexpectedValueException('value must be null or a string in UUID format');

    $this->id = $value;
  }

  public function setNotes(string $value): void
  {
    if (strlen($value) > self::MAX_NOTES)
      throw new UnexpectedValueException(sprintf('value must be between 0-%d characters', self::MAX_NOTES));

    $this->notes = $value;
  }

  public function setOption(int $option, bool $value): void
  {
    if ($value) $this->options |= $option;
    else $this->options &= ~$option;
  }

  public function setOptions(int $value): void
  {
    if ($value < 0 || $value > self::MAX_OPTIONS)
      throw new UnexpectedValueException(sprintf('value must be an integer between range 0-%d', self::MAX_OPTIONS));

    $this->options = $value;
  }

  public function setPlexEmail(?string $value, bool $auto_null = true): void
  {
    if ($auto_null && is_string($value) && empty($value)) $value = null;

    if (is_string($value) && strlen($value) > self::MAX_PLEX_EMAIL)
      throw new UnexpectedValueException(sprintf('value must be null or a string between 1-%d characters', self::MAX_PLEX_EMAIL));

    $this->plex_email = $value;
  }

  public function setPlexId(?int $value): void
  {
    if (!is_null($value) && ($value < 0 || $value > self::MAX_PLEX_ID))
      throw new UnexpectedValueException(sprintf('value must be null or an integer between 0-%d', self::MAX_PLEX_ID));

    $this->plex_id = $value;
  }

  public function setPlexThumb(?string $value, bool $auto_null = true): void
  {
    if ($auto_null && is_string($value) && empty($value)) $value = null;
  
    if (is_string($value) && strlen($value) > self::MAX_PLEX_THUMB)
      throw new UnexpectedValueException(sprintf('value must be null or a string between 1-%d characters', self::MAX_PLEX_THUMB));

    $this->plex_thumb = $value;
  }

  public function setPlexTitle(?string $value, bool $auto_null = true): void
  {
    if ($auto_null && is_string($value) && empty($value)) $value = null;

    if (is_string($value) && strlen($value) > self::MAX_PLEX_TITLE)
      throw new UnexpectedValueException(sprintf('value must be null or a string between 1-%d characters', self::MAX_PLEX_TITLE));

    $this->plex_title = $value;
  }

  public function setPlexUsername(?string $value, bool $auto_null = true): void
  {
    if ($auto_null && is_string($value) && empty($value)) $value = null;

    if (is_string($value) && strlen($value) > self::MAX_PLEX_USERNAME)
      throw new UnexpectedValueException(sprintf('value must be null or a string between 1-%d characters', self::MAX_PLEX_USERNAME));

    $this->plex_username = $value;
  }

  public function setRecordUpdated(DateTimeInterface|string $value): void
  {
    $this->record_updated = (is_string($value) ? new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : $value);
  }

  public function setRisk(int $value): void
  {
    if ($value < self::RISK_UNASSESSED || $value > self::RISK_HIGH)
      throw new UnexpectedValueException(sprintf('value must be an integer between range %d-%d', self::RISK_UNASSESSED, self::RISK_HIGH));

    $this->risk = $value;
  }

  public function setUser(BaseUser|null $value): void
  {
    $this->setUserId(is_null($value) ? null : $value->getId());
  }

  public function setUserId(string|null $value, bool $auto_null = true): void
  {
    if ($auto_null && is_string($value) && empty($value)) $value = null;

    if (is_string($value) && preg_match(self::UUID_REGEX, $value) !== 1)
      throw new UnexpectedValueException('value must be null or a string in UUID format');

    $this->user_id = $value;
  }
}
