<?php

namespace CarlBennett\Tools\Models\Plex;

use \CarlBennett\Tools\Models\ActiveUser as ActiveUserModel;

class Users extends ActiveUserModel {

  public $hl; // highlight
  public $id;
  public $show_hidden;
  public $users;

}
