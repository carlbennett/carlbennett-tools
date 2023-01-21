<?php
declare(strict_types=1);

namespace CarlBennett\Tools\Libraries\User;

use \CarlBennett\Tools\Libraries\Database;
use \CarlBennett\Tools\Libraries\User\User;
use \InvalidArgumentException;
use \PDO;
use \StdClass;
use \UnexpectedValueException;

class Acl implements \CarlBennett\Tools\Interfaces\DatabaseObject, \JsonSerializable
{
  public const ACL_PASTEBIN_ADMIN = 'pastebin.admin';
  public const ACL_PHPINFO = 'phpinfo';
  public const ACL_PLEX_USERS = 'plex.users';
  public const ACL_USERS_INVITE = 'users.invite';
  public const ACL_USERS_MANAGE = 'users.manage';
  public const ACL_WHOIS_SERVICE = 'whois.service';

  # Maximum SQL field lengths, alter as appropriate.
  public const MAX_ACL_ID = 0xFF;

  protected string $user_id;
  protected array $acls;

  public function __construct(StdClass|string $value)
  {
    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
      return;
    }

    $this->setUserId($value);
    if (!$this->allocate()) throw new UnexpectedValueException();
  }

  public function allocate(): bool
  {
    $this->setAcls([]);

    $id = $this->getUserId();
    if (is_null($id)) return true;

    $q = Database::instance()->prepare('SELECT `acl_id` FROM `user_acls` WHERE `user_id` = UuidToBin(?);');
    if (!$q || !$q->execute([$id])) return false;

    $r = new StdClass();
    $r->acls = $q->fetchAll(PDO::FETCH_COLUMN, 0);
    $r->user_id = $id;

    $q->closeCursor();
    $this->allocateObject($r);
    return true;
  }

  protected function allocateObject(StdClass $value): void
  {
    $this->setAcls($value->acls);
    $this->setUserId($value->user_id);
  }

  public function commit(): bool
  {
    $q1 = Database::instance()->prepare('DELETE FROM `user_acls` WHERE `user_id` = UuidToBin(:uid);');
    $q2 = Database::instance()->prepare('INSERT INTO `user_acls` (`user_id`, `acl_id`) VALUES (UuidToBin(:uid), :aid);');

    $user_id = $this->getUserId();
    if (!$q1 || !$q1->execute([':uid' => $user_id])) return false;
    if ($q1) $q1->closeCursor();

    foreach ($this->acls as $acl_id => $enable)
    {
      if (!$enable) continue;
      if (!$q2 || !$q2->execute([':aid' => $acl_id, ':uid' => $user_id])) return false;
      if ($q2) $q2->closeCursor();
    }

    return true;
  }

  public function deallocate(): bool
  {
    $id = $this->getUserId();
    if (is_null($id)) return false;
    $q = Database::instance()->prepare('DELETE FROM `user_acls` WHERE `user_id` = UuidToBin(?);');
    try { return $q && $q->execute([$id]); }
    finally { if ($q) $q->closeCursor(); }
  }

  /**
   * @param string $value The Access Control List identifier.
   * @return boolean Whether the Access Control List identifier is set.
   */
  public function getAcl(string $value): bool
  {
    return isset($this->acls[$value]) && $this->acls[$value] == true;
  }

  /**
   * @return string The Access Control List identifiers.
   */
  public function getAcls(): array
  {
    $acls = [];

    foreach ($this->acls as $acl_id => $enable)
    {
      if (!$enable) continue;
      $acls[] = $acl_id;
    }

    return $acls;
  }

  public function getUser(): ?User
  {
    return is_null($this->user_id) ? null : new User($this->user_id);
  }

  /**
   * @return string The UUID in hexadecimal string format (with dashes).
   */
  public function getUserId(): ?string
  {
    return $this->user_id;
  }

  public function jsonSerialize(): mixed
  {
    return [
      'acls' => $this->getAcls(),
      'user' => $this->getUser(),
    ];
  }

  /**
   * @param string $value The Access Control List identifier.
   * @param boolean $enable Whether to enable or disable the Access Control.
   */
  public function setAcl(string $value, bool $enable): void
  {
    if (strlen($value) > self::MAX_ACL_ID)
    {
      throw new InvalidArgumentException(sprintf(
        'value must be less than or equal to %d characters', self::MAX_ACL_ID
      ));
    }

    if (!$enable)
    {
      unset($this->acls[$value]);
    }
    else
    {
      $this->acls[$value] = $enable;
    }
  }

  /**
   * @param array $value The Access Control List identifiers.
   */
  public function setAcls(array $value): void
  {
    $this->acls = [];

    foreach ($value as $acl)
    {
      $this->setAcl($acl, true);
    }
  }

  /**
   * @param string $value The UUID in hexadecimal format (with dashes) to set.
   *                      Example: "31952e1c-d05a-44a0-b749-6d892cc96d3a"
   */
  public function setUserId(string $value): void
  {
    if (preg_match(self::UUID_REGEX, $value) !== 1)
    {
      // InvalidArgumentException is in Logic
      throw new InvalidArgumentException(
        'value must be a UUID formatted string'
      );
    }

    $this->user_id = $value;
  }
}
