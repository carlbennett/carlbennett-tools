<?php

namespace CarlBennett\Tools\Controllers\Plex\Users;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\Plex\User as PlexUser;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Models\Plex\Users\Edit as EditModel;

class Edit extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $form = $router->getRequestBodyArray();
    $model = new EditModel();
    $query = $router->getRequestQueryArray();
    $user = Authentication::$user;

    if (!$user || !($user->getOptionsBitmask() & User::OPTION_ACL_PLEX_USERS)) {
      $model->user = false;
      $view->render($model);
      $model->_responseCode = 401;
      return $model;
    }

    $id = (isset($query['id']) ? $query['id'] : null);
    if (!empty($id)) {
      $model->user = new PlexUser($id);
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
