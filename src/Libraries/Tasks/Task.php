<?php

namespace CarlBennett\Tools\Libraries\Tasks;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\Tools\Models\Task as TaskModel;
use \UnexpectedValueException;

abstract class Task
{
  public static function create(TaskModel $model)
  {
    switch (strtolower($model->task_name))
    {
      case 'prune_user_sessions': return new PruneUserSessionsTask($model);
      case 'test': return new TestTask($model);
      default: throw new UnexpectedValueException('invalid task name');
    }
  }

  public function __construct(TaskModel $model)
  {
    $this->model = &$model;
  }

  public abstract function run();
}
