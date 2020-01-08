<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\User\Login as LoginModel;

class Login extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new LoginModel();

    $form = $router->getRequestBodyArray();
    $model->email = (isset($form['email']) ? $form['email'] : null);
    $model->password = (isset($form['password']) ? $form['password'] : null);

    if ($router->getRequestMethod() == 'POST') {
      $model->error = 'Incorrect password.';
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
