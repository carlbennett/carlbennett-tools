<?php

namespace CarlBennett\Tools\Models;

use \CarlBennett\MVC\Libraries\Model;

class Task extends Model
{
  public $auth_token;
  public $auth_token_valid;
  public $task_name;
  public $task_result;
}
