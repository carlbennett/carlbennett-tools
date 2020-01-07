<?php

namespace CarlBennett\Tools\Controllers\Plex;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Libraries\Plex\User;
use \CarlBennett\Tools\Models\Plex\Users as UsersModel;

class Users extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UsersModel();

    $model->users = User::getAll();

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
