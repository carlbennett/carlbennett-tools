<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Tasks\Task;
use \CarlBennett\Tools\Libraries\Utility\HTTPForm;
use \CarlBennett\Tools\Models\Task as TaskModel;
use \UnexpectedValueException;

class BackendTask extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new TaskModel();
    $model->_responseCode = 500;
    $model->task_name = array_shift($args);

    self::auth($router, $model);
    if (!$model->auth_token_valid)
    {
      $model->_responseCode = 401;
      $model->task_result = '401 Bad Auth Token';
      $view->render($model);
      return $model;
    }

    try
    {
      $task = Task::create($model);
    }
    catch (UnexpectedValueException $e) // invalid task name
    {
      $task = null;
    }

    if (!$task)
    {
      $model->_responseCode = 404;
      $model->task_result = '404 Task Not Found';
    }
    else
    {
      $task->run();
    }

    $view->render($model);
    return $model;
  }

  protected static function auth($router, $model)
  {
    $g = new HTTPForm($router->getRequestQueryArray());
    $p = new HTTPForm($router->getRequestBodyArray());
    $h = getenv('HTTP_X_AUTH_TOKEN');

    $model->auth_token = $p->get('auth_token') ??
      $g->get('auth_token') ?? $h ?? null;

    $cnf_auth_token = Common::$config->backend_tasks->auth_token;
    $model->auth_token_valid = ($model->auth_token === $cnf_auth_token);
  }
}
