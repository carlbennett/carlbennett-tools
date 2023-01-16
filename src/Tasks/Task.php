<?php

namespace CarlBennett\Tools\Tasks;

use \CarlBennett\Tools\Models\Task as TaskModel;

abstract class Task
{
  public TaskModel $model;

  /**
   * Creates a Task subclass from the provided task name string in the model object.
   */
  public static function create(TaskModel $model): self
  {
    switch (strtolower($model->task_name))
    {
      case 'prune_user_sessions': return new PruneUserSessionsTask($model);
      case 'sync_plex_users': return new SyncPlexUsersTask($model);
      case 'test': return new TestTask($model);
      default: throw new \UnexpectedValueException('Invalid task name');
    }
  }

  public function __construct(TaskModel $model)
  {
    $this->model = &$model;
  }

  public abstract function run(): void;
}
