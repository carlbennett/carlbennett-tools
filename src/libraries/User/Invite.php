<?php
declare(strict_types=1);
namespace CarlBennett\Tools\Libraries\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\Tools\Libraries\IDatabaseObject;
use \CarlBennett\Tools\Libraries\User;

use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \StdClass;
use \UnexpectedValueException;

class Invite implements IDatabaseObject
{

  const DATE_SQL = 'Y-m-d H:i:s';

  # Maximum SQL field lengths, alter as appropriate.
  const MAX_EMAIL = 191;

  const UUID_REGEX = '/([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}/';

  private $_id;

  protected $date_accepted;
  protected $date_invited;
  protected $date_revoked;
  protected $email;
  protected $id;
  protected $invited_by;
  protected $invited_user;
  protected $record_updated;

  /**
   * Creates an instance of a user invite based by invite id or StdClass object.
   *
   * @param mixed $value The id string to lookup or StdClass object to copy.
   * @throws InvalidArgumentException when value is not: null, string, or StdClass object.
   */
  public function __construct($value)
  {
    if (is_null($value) || is_string($value))
    {
      $this->_id = $value;
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
    $id = $this->_id;

    if (!(is_null($id) || is_string($id)))
    {
      throw new InvalidArgumentException('value must be null or a string');
    }

    if (empty($id)) return;
    $this->setId($id);

    if (!isset(Common::$database))
    {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $q = Common::$database->prepare(
      'SELECT
        `date_accepted`, `date_invited`, `date_revoked`, `email`,
        UuidFromBin(`id`) AS `id`, `invited_by`, `invited_user`,
        `record_updated`
      FROM `user_invites` WHERE id = UuidToBin(:id) LIMIT 1;'
    );
    $q->bindParam(':id', $id, PDO::PARAM_STR);

    $r = $q->execute();
    if (!$r)
    {
      throw new UnexpectedValueException('an error occurred finding invite id');
    }

    if ($q->rowCount() != 1)
    {
      throw new UnexpectedValueException(sprintf(
        'invite id: %s not found', $id
      ));
    }

    $r = $q->fetchObject();
    $q->closeCursor();

    $this->allocateObject($r);
  }

  /**
   * Copies a database object into this object's properties.
   *
   * @param StdClass $value The database object to copy into properties.
   */
  protected function allocateObject(StdClass $value)
  {
    $tz = new DateTimeZone('Etc/UTC');

    $this->setDateInvited(new DateTime($value->date_invited, $tz));
    $this->setDateRevoked(
      $value->date_revoked ? new DateTime($value->date_revoked, $tz) : null
    );
    $this->setDateAccepted(
      $value->date_accepted ? new DateTime($value->date_accepted, $tz) : null
    );
    $this->setEmail($value->email);
    $this->setId($value->id);
    $this->setInvitedBy(
      $value->invited_by ? new User($value->invited_by) : null
    );
    $this->setInvitedUser(
      $value->invited_user ? new User($value->invited_user) : null
    );
    $this->setRecordUpdated(new DateTime($value->record_updated, $tz));
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

    if (empty($this->id))
    {
      $q = Common::$database->query('SELECT UUID();');
      if (!$q) return $q;

      $this->id = $q->fetch(PDO::FETCH_NUM)[0];
      $q->closeCursor();
    }

    $q = Common::$database->prepare(
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

    $date_accepted = (
      is_null($this->date_accepted) ?
      null : $this->date_accepted->format(self::DATE_SQL)
    );

    $date_invited = $this->date_invited->format(self::DATE_SQL);

    $date_revoked = (
      is_null($this->date_revoked) ?
      null : $this->date_revoked->format(self::DATE_SQL)
    );

    $invited_by = (
      is_null($this->invited_by) ? null : $this->invited_by->getId()
    );

    $invited_user = (
      is_null($this->invited_user) ? null : $this->invited_user->getId()
    );

    $record_updated = $this->record_updated->format(self::DATE_SQL);

    $q->bindParam(':accepted', $date_accepted, (
      is_null($date_accepted) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':invited', $date_invited, PDO::PARAM_STR);

    $q->bindParam(':revoked', $date_revoked, (
      is_null($date_revoked) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':email', $this->email, PDO::PARAM_STR);
    $q->bindParam(':id', $this->id, PDO::PARAM_STR);

    $q->bindParam(':invited_by', $invited_by, (
      is_null($invited_by) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':invited_user', $invited_user, (
      is_null($invited_user) ? PDO::PARAM_NULL : PDO::PARAM_STR
    ));

    $q->bindParam(':record_updated', $record_updated, PDO::PARAM_STR);

    $r = $q->execute();
    if (!$r) return $r;

    $q->closeCursor();
    return $r;
  }

  /**
   * @return ?DateTime The DateTime object or null value.
   */
  public function getDateAccepted()
  {
    return $this->date_accepted;
  }

  /**
   * @return DateTime The DateTime object.
   */
  public function getDateInvited()
  {
    return $this->date_invited;
  }

  /**
   * @return ?DateTime The DateTime object or null value.
   */
  public function getDateRevoked()
  {
    return $this->date_revoked;
  }

  /**
   * @return string The email address string value.
   */
  public function getEmail()
  {
    return $this->email;
  }

  /**
   * @return string The UUID in hexadecimal string format (with dashes).
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return ?User The User object or null value.
   */
  public function getInvitedBy()
  {
    return $this->invited_by;
  }

  /**
   * @return ?User The User object or null value.
   */
  public function getInvitedUser()
  {
    return $this->invited_user;
  }

  /**
   * @return DateTime The DateTime object.
   */
  public function getRecordUpdated()
  {
    return $this->record_updated;
  }

  /**
   * @param ?DateTime $value The DateTime object or null value to set.
   */
  public function setDateAccepted(?DateTime $value)
  {
    $this->date_accepted = $value;
  }

  /**
   * @param DateTime $value The DateTime object to set.
   */
  public function setDateInvited(DateTime $value)
  {
    if (is_null($value))
    {
      // InvalidArgumentException is in Logic
      throw new InvalidArgumentException('value cannot be null');
    }

    $this->date_invited = $value;
  }

  /**
   * @param ?DateTime $value The DateTime object or null value to set.
   */
  public function setDateRevoked(?DateTime $value)
  {
    $this->date_revoked = $value;
  }

  /**
   * @param string $value The valid email address string to set.
   * @param bool $force If true, skips check with filter_var(). Default: false
   */
  public function setEmail(string $value, bool $force = false)
  {
    if (!$force && !filter_var($value, FILTER_VALIDATE_EMAIL))
    {
      // UnexpectedValueException is at Runtime
      throw new UnexpectedValueException('value is not a valid email address');
    }

    if (strlen($value) > self::MAX_EMAIL)
    {
      // UnexpectedValueException is at Runtime
      throw new UnexpectedValueException(sprintf(
        'value string length must be less than or equal to %d', self::MAX_EMAIL
      ));
    }

    $this->email = $value;
  }

  /**
   * @param string $value The UUID in hexadecimal format (with dashes) to set.
   *                      Example: "31952e1c-d05a-44a0-b749-6d892cc96d3a"
   */
  public function setId(string $value)
  {
    if (preg_match(self::UUID_REGEX, $value) !== 1)
    {
      // InvalidArgumentException is in Logic
      throw new InvalidArgumentException(
        'value must be a UUID formatted string'
      );
    }

    $this->id = $value;
  }

  /**
   * @param ?User $value The User object or null value to set.
   */
  public function setInvitedBy(?User $value)
  {
    $this->invited_by = $value;
  }

  /**
   * @param ?User $value The User object or null value to set.
   */
  public function setInvitedUser(?User $value)
  {
    $this->invited_user = $value;
  }

  /**
   * @param DateTime $value The DateTime object to set.
   */
  public function setRecordUpdated(DateTime $value)
  {
    if (is_null($value))
    {
      // InvalidArgumentException is in Logic
      throw new InvalidArgumentException('value cannot be null');
    }

    $this->record_updated = $value;
  }

}
