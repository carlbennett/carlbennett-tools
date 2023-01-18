<?php

namespace CarlBennett\Tools\Libraries;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\Tools\Libraries\Database;
use \CarlBennett\Tools\Libraries\User;
use \DateTime;
use \DateTimeInterface;
use \DateTimeZone;
use \InvalidArgumentException;
use \LengthException;
use \PDO;
use \StdClass;
use \UnexpectedValueException;

class PasteObject implements \CarlBennett\Tools\Interfaces\DatabaseObject
{
  const DATE_SQL = 'Y-m-d H:i:s';

  # Maximum SQL field lengths, alter as appropriate.
  const MAX_CONTENT = 4294967295; // 4 GiB
  const MAX_TITLE   = 191;

  const OPTION_QUARANTINE = 0x00000001;
  const OPTION_UNLISTED   = 0x00000002;

  protected string $content;
  protected DateTimeImmutable $date_added;
  protected ?DateTimeImmutable $date_expires;
  protected ?string $id;
  protected string $mimetype;
  protected int $options_bitmask;
  protected ?string $password_hash;
  protected string $title;
  protected ?string $user_id;

  public function __construct($value)
  {
    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
    }
    else
    {
      $this->id = $value;
      if (!$this->allocate()) throw new InvalidArgumentException();
    }
  }

  public function allocate(): bool
  {
    $this->setDateAdded(new DateTime('now', new DateTimeZone('Etc/UTC')));
    $this->setDateExpires(null);

    $id = $this->getId();
    if (empty($id)) return true;

    $q = Database::instance()->prepare('
      SELECT
        `content`, `date_added`, `date_expires`, UuidFromBin(`id`) AS `id`,
        `mimetype`, `options_bitmask`, `password_hash`, `title`,
        UuidFromBin(`user_id`) AS `user_id`
      FROM `pastebin` WHERE `id` = UuidToBin(:id) LIMIT 1;
    ');

    try
    {
      if (!$q || !$q->execute([':id' => $id]))
        throw new UnexpectedValueException('an error occurred finding the paste');

      if ($q->rowCount() != 1)
        throw new UnexpectedValueException(sprintf('paste id: %s not found', $id));

      $this->allocateObject($q->fetchObject());
      return true;
    }
    finally { if ($q) $q->closeCursor(); }
  }

  protected function allocateObject(StdClass $value): void
  {
    $this->setContent($value->content);
    $this->setDateAdded($value->date_added);
    $this->setDateExpires($value->date_expires);
    $this->setId($value->id);
    $this->setMimetype($value->mimetype);
    $this->setOptionsBitmask($value->options_bitmask);
    $this->setPasswordHash($value->password_hash);
    $this->setTitle($value->title);
    $this->setUserId($value->user_id);
  }

  public function checkPassword(string $password): bool
  {
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

  public function commit(): bool
  {
    if (empty($this->id)) $this->id = \Ramsey\Uuid\Uuid::uuid4();

    $q = Database::instance()->prepare('
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

  public static function createPassword(string $password): string
  {
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

  public function deallocate(): bool
  {
    $id = $this->getId();
    if (\is_null($id)) return false;
    $q = Database::instance()->prepare('DELETE FROM `pastebin` WHERE `id` = UuidToBin(?) LIMIT 1;');
    try { return $q && $q->execute([$id]); }
    finally { if ($q) $q->closeCursor(); }
  }

  public function getContent(): string
  {
    return $this->content;
  }

  public function getDateAdded(): DateTimeInterface
  {
    return $this->date_added;
  }

  public function getDateExpires(): ?DateTimeInterface
  {
    return $this->date_expires;
  }

  public function getId(): string
  {
    return $this->id;
  }

  public function getMimetype(): string
  {
    return $this->mimetype;
  }

  public function getOptionsBitmask(): int
  {
    return $this->options_bitmask;
  }

  public function getPasswordHash(): string
  {
    return $this->password_hash;
  }

  public static function getRecentPastes($limit = 10, $bitmask = null, $passworded = false): ?array
  {
    if (\is_null($bitmask) || !\is_numeric($bitmask))
      $bitmask = (self::OPTION_QUARANTINE | self::OPTION_UNLISTED);

    $q = Database::instance()->prepare(sprintf('
      SELECT UuidFromBin(`id`) AS `id` FROM `pastebin`
      WHERE %s NOT (`options_bitmask` & %d)
      ORDER BY `date_added` DESC LIMIT %d;
    ', (!$passworded ? '`password_hash` IS NULL AND' : ''), $bitmask, $limit));

    try
    {
      if (!$q || !$q->execute())
        throw new UnexpectedValueException('an error occurred finding public pastes');

      $pastes = [];
      while ($r = $q->fetchObject()) $pastes[] = new self($r->id);
      return $pastes;
    }
    finally { if ($q) $q->closeCursor(); }
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function getURI(): string
  {
    return Common::relativeUrlToAbsolute('/paste/' . $this->id);
  }

  public function getUser(): ?User
  {
    return \is_null($this->user_id) ? null : new User($this->user_id);
  }

  public function getUserId(): ?string
  {
    return $this->user_id;
  }

  public function setContent(string $value): void
  {
    if (!is_string($value)) throw new InvalidArgumentException('value must be a string');

    $this->content = $value;
  }

  public function setDateAdded(DateTimeInterface|string $value): void
  {
    $this->date_added = \is_null($value) ? null : (
      \is_string($value) ? new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : DateTimeImmutable::createFromInterface($value)
    );
  }

  public function setDateExpires(DateTimeInterface|string|null $value): void
  {
    $this->date_expires = \is_null($value) ? null : (
      \is_string($value) ? new DateTimeImmutable($value, new DateTimeZone(self::DATE_TZ)) : DateTimeImmutable::createFromInterface($value)
    );
  }

  public function setId(string $value): void
  {
    if (!\is_string($value) || \preg_match(self::UUID_REGEX, $value) !== 1)
      throw new InvalidArgumentException('value must be a string in UUID format');

    $this->id = $value;
  }

  public function setMimetype(string $value): void
  {
    if (!\is_string($value) || empty($value))
      throw new InvalidArgumentException('value must be a non-empty string in mimetype format');

    $this->mimetype = $value;
  }

  public function setOptionsBitmask(int $value): void
  {
    $this->options_bitmask = $value;
  }

  public function setPasswordHash(?string $value): void
  {
    if (!(\is_null($value) || \is_string($value)))
      throw new InvalidArgumentException('value must be null or a string');

    if (!\is_null($value) && empty($value))
      throw new LengthException('value must be non-empty');

    $this->password_hash = $value;
  }

  public function setTitle(string $value): void
  {
    if (!\is_string($value))
      throw new InvalidArgumentException('value must be a string');

    $this->title = $value;
  }

  public function setUser(?User $value): void
  {
    if (!(\is_null($value) || $value instanceof User))
      throw new InvalidArgumentException('value must be null or instance of User');

    $this->user_id = (is_null($value) ? $value : $value->getId());
  }

  public function setUserId(?string $value): void
  {
    if (!(\is_null($value) || (\is_string($value) && \preg_match(self::UUID_REGEX, $value) === 1)))
      throw new InvalidArgumentException('value must be null or a string in UUID format');

    $this->user_id = $value;
  }
}
