<?php

namespace CarlBennett\Tools\Controllers\Plex;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Models\Plex\Requests as PlexRequestsModel;

class Requests extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new PlexRequestsModel();
    $model->active_user = Authentication::$user;

    if (!($model->active_user &&
      $model->active_user->getOption(User::OPTION_ACL_PLEX_REQUESTS)
    )) {
      $view->render($model);
      $model->_responseCode = 401;
      return $model;
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
