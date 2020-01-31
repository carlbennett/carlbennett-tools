<?php

namespace CarlBennett\Tools\Controllers\Plex\Users;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\Plex\User as PlexUser;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Models\Plex\Users\Edit as EditModel;

use \Exception;

class Edit extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new EditModel();
    $model->user = Authentication::$user;

    $form = $router->getRequestBodyArray();
    $query = $router->getRequestQueryArray();

    if (!$model->user ||
      !($model->user->getOptionsBitmask() & User::OPTION_ACL_PLEX_USERS)) {
      $view->render($model);
      $model->_responseCode = 401;
      return $model;
    }

    $id = (isset($query['id']) ? $query['id'] : null);
    if (!empty($id)) {
      try {
        $model->plex_user = new PlexUser($id);
      } catch (Exception $e) {
        $model->plex_user = null;
      }
    }

    if (!$model->plex_user) {
      $view->render($model);
      $model->_responseCode = 404;
      return $model;
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
