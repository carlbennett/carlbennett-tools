<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Libraries\User\Invite;
use \CarlBennett\Tools\Models\User\Profile as ProfileModel;

class Profile extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new ProfileModel();
    $model->active_user = Authentication::$user;

    if (!$model->active_user) {
      $model->_responseCode = 401;
      $view->render($model);
      return $model;
    }

    $model->_responseCode = 200;
    $model->feedback = array(); // for bootstrap field/color
    $query = $router->getRequestQueryArray();

    $return = $query['return'] ?? null;
    if (!empty($return) && substr($return, 0, 1) != '/') $return = null;
    if (!empty($return)) $return = Common::relativeUrlToAbsolute($return);
    $model->return = $return;

    if ($router->getRequestMethod() == 'POST') {
      $this->processProfile($router, $model);
    }

    if (!empty($model->return)) {
      $model->_responseCode = 303;
      header('Location: ' . $model->return);
    }

    $view->render($model);
    return $model;
  }

  protected function processProfile(Router &$router, ProfileModel &$model) {
    $data = $router->getRequestBodyArray();
  }
}
