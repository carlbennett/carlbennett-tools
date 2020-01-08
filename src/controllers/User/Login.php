<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\User\Login as LoginModel;

class Login extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new LoginModel();
    $model->feedback = array();

    $form = $router->getRequestBodyArray();
    $model->email = (isset($form['email']) ? $form['email'] : null);
    $model->password = (isset($form['password']) ? $form['password'] : null);

    if ($router->getRequestMethod() == 'POST') {
      $this->processLogin($model);
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function processLogin(LoginModel &$model) {
    if (empty($model->email)) {
      $model->feedback['email'] = 'Email cannot be empty.';
      return;
    }

    if (!filter_var($model->email, FILTER_VALIDATE_EMAIL)) {
      $model->feedback['email'] = 'Invalid email address.';
      return;
    }

    $model->feedback['password'] = 'Incorrect password.';
  }
}
