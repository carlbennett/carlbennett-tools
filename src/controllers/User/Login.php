<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\User\Login as LoginModel;

class Login extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new LoginModel();
    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
