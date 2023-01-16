<?php

namespace CarlBennett\Tools\Models;

class Paste extends ActiveUser
{
  public $id;
  public $paste_object;
  public $pastebin_admin;
  public $recent_pastes;
}
