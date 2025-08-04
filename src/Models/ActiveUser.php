<?php

namespace CarlBennett\Tools\Models;

class ActiveUser extends Errorable implements \JsonSerializable
{
  /**
   * The current user that is logged in to the site, or null if not logged in.
   *
   * @var \CarlBennett\Tools\Libraries\User\User|null
   */
  public ?\CarlBennett\Tools\Libraries\User\User $active_user = null;

  /**
   * When constructed, sets the $active_user to that of the Authentication::$user value.
   * Child classes that override __construct() must call parent::__construct().
   */
  public function __construct()
  {
    $this->active_user = &\CarlBennett\Tools\Libraries\Core\Authentication::$user;
  }

  /**
   * Implements the JSON serialization function from the JsonSerializable interface.
   */
  public function jsonSerialize() : mixed
  {
    return \array_merge(['active_user' => $this->active_user], parent::jsonSerialize());
  }
}
