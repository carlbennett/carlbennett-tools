<?php
declare(strict_types=1);
namespace CarlBennett\Tools\Libraries\User;

use \DateTime;
use \InvalidArgumentException;
use \UnexpectedValueException;

class Invite
{

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

    $this->email = $value;
  }

  /**
   * @param string $value The UUID in hexadecimal format (with dashes) to set.
   *                      Example: "31952e1c-d05a-44a0-b749-6d892cc96d3a"
   */
  public function setId(string $value)
  {
    if (!preg_match(self::UUID_REGEX, $value) !== 1)
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
