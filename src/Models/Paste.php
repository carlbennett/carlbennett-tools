<?php

namespace CarlBennett\Tools\Models;

use \CarlBennett\Tools\Models\ActiveUser as ActiveUserModel;

class Paste extends ActiveUserModel {

  public $error;
  public $id;
  public $paste_object;
  public $pastebin_admin;
  public $recent_pastes;

}
