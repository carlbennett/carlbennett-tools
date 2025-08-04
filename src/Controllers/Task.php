<?php

namespace CarlBennett\Tools\Controllers;

class Task extends \CarlBennett\Tools\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\Task();
  }

  public function invoke(?array $args): bool
  {
    if (\is_null($args) || \count($args) < 1) throw new \InvalidArgumentException();

    $this->model->_responseCode = 500;
    $this->model->task_name = \array_shift($args);

    self::auth($this->model);
    if (!$this->model->auth_token_valid)
    {
      $this->model->_responseCode = 401;
      $this->model->task_result = '401 Bad Auth Token';
      return true;
    }

    try
    {
      $task = \CarlBennett\Tools\Tasks\Task::create($this->model);
    }
    catch (\UnexpectedValueException) // invalid task name
    {
      $task = null;
    }

    if (!$task)
    {
      $this->model->_responseCode = 404;
      $this->model->task_result = '404 Task Not Found';
    }
    else
    {
      $task->run();
    }

    return true;
  }

  protected static function auth($model): void
  {
    $q = new \CarlBennett\Tools\Libraries\Utility\HTTPForm(\CarlBennett\Tools\Libraries\Core\Router::query());
    $h = \getenv('HTTP_X_AUTH_TOKEN');

    $model->auth_token = $q->get('auth_token') ?? $h ?? null;

    $cnf_auth_token = \CarlBennett\Tools\Libraries\Core\Config::instance()->root['tasks']['auth_token'];
    $model->auth_token_valid = ($model->auth_token === $cnf_auth_token);
  }
}
