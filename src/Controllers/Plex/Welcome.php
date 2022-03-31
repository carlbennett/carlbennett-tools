<?php

namespace CarlBennett\Tools\Controllers\Plex;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Models\Plex\Welcome as PlexWelcomeModel;

class Welcome extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new PlexWelcomeModel();
    $model->active_user = Authentication::$user;

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
