<?php
declare(strict_types=1);
namespace CarlBennett\Tools\Libraries\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\Tools\Libraries\IDatabaseObject;
use \CarlBennett\Tools\Libraries\User;

use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;
use \UnexpectedValueException;

class Acl implements IDatabaseObject
{

  const ACL_PASTEBIN_ADMIN = 'pastebin.admin';
  const ACL_PHPINFO = 'phpinfo';
  const ACL_PLEX_USERS = 'plex.users';
  const ACL_USERS_INVITE = 'users.invite';
  const ACL_USERS_MANAGE = 'users.manage';
  const ACL_WHOIS_SERVICE = 'whois.service';

  # Maximum SQL field lengths, alter as appropriate.
  const MAX_ACL_ID = 255;

  const UUID_REGEX = '/([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}/';

  private $_user_id;

  protected $user_id;
  protected $acls;

  /**
   * Creates an instance of a user acl based by user id or StdClass object.
   *
   * @param mixed $value The id string to lookup or StdClass object to copy.
   * @throws InvalidArgumentException when value is not: null, string, or StdClass object.
   */
  public function __construct($value)
  {
    if (is_null($value) || is_string($value))
    {
      $this->_user_id = $value;
      $this->allocate();
      return;
    }

    if ($value instanceof StdClass)
    {
      $this->allocateObject($value);
      return;
    }

    throw new InvalidArgumentException('value must be a string or StdClass');
  }

  /**
   * Queries the database for the _id value and copies the object to properties.
   * Inherited from the IDatabaseObject interface.
   *
   * @throws InvalidArgumentException when value must be null or a string
   * @throws UnexpectedValueException when an error occurred finding invite id
   * @throws UnexpectedValueException when invite id is not found
   */
  public function allocate()
  {
    $id = $this->_user_id;

    if (!(is_null($id) || is_string($id)))
    {
      throw new InvalidArgumentException('value must be null or a string');
    }

    if (empty($id)) return;
    $this->setUserId($id);

    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare(
      'SELECT `acl_id` FROM `user_acls` WHERE user_id = UuidToBin(:id);'
    );
    $q->bindParam(':id', $id, PDO::PARAM_STR);

    $r = $q->execute();
    if (!$r)
    {
      throw new UnexpectedValueException('an error occurred finding user acls');
    }

    $acls = $q->fetchAll(PDO::FETCH_COLUMN, 0);
    $q->closeCursor();

    $r = new StdClass();
    $r->acls = $acls;
    $r->user_id = $id;

    $this->allocateObject($r);
  }

  /**
   * Copies a database object into this object's properties.
   *
   * @param StdClass $value The database object to copy into properties.
   */
  protected function allocateObject(StdClass $value)
  {
    $this->setAcls($value->acls);
    $this->setUserId($value->user_id);
  }

  /**
   * Commits the current properties to the database object.
   * Inherited from the IDatabaseObject interface.
   */
  public function commit()
  {
    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q1 = Common::$database->prepare(
      'DELETE FROM `user_acls` WHERE `user_id` = UuidToBin(:uid);'
    );
    $q2 = Common::$database->prepare(
      'INSERT INTO `user_acls` (`user_id`, `acl_id`)
      VALUES (UuidToBin(:uid), :aid);'
    );

    $q1->bindParam(':uid', $this->user_id, (
      is_null($this->user_id) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $r = $q1->execute();
    if (!$r) return $r;
    $q1->closeCursor();

    foreach ($this->acls as $acl_id => $enable)
    {
      if (!$enable) continue;

      $q2->bindParam(':uid', $this->user_id, (
        is_null($this->user_id) ? PDO::PARAM_NULL : PDO::PARAM_STR
      ));
      $q2->bindParam(':aid', $acl_id, PDO::PARAM_STR);

      $r = $q2->execute();
      if (!$r) return $r;
      $q2->closeCursor();
    }

    return $r;
  }

  /**
   * @param string $value The Access Control List identifier.
   * @return string Whether the Access Control List identifier is set.
   */
  public function getAcl(string $value)
  {
    return isset($this->acls[$value]) && $this->acls[$value] == true;
  }

  /**
   * @return string The Access Control List identifiers.
   */
  public function getAcls()
  {
    $acls = [];

    foreach ($this->acls as $acl_id => $enable)
    {
      if (!$enable) continue;
      $acls[] = $acl_id;
    }

    return $acls;
  }

  /**
   * @return string The UUID in hexadecimal string format (with dashes).
   */
  public function getUserId()
  {
    return $this->user_id;
  }

  /**
   * @param string $value The Access Control List identifier.
   * @param bool $enable Whether to enable or disable the Access Control.
   */
  public function setAcl(string $value, bool $enable)
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
   * @param string $value The Access Control List identifiers.
   */
  public function setAcls(array $value)
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
  public function setUserId(string $value)
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
