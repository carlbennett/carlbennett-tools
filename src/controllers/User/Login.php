<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Models\User\Authentication as AuthModel;

class Login extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new AuthModel();
    $model->feedback = array();

    $form = $router->getRequestBodyArray();
    $model->email = (isset($form['email']) ? $form['email'] : null);
    $model->password = (isset($form['password']) ? $form['password'] : null);

    $query = $router->getRequestQueryArray();
    $return = (isset($query['return']) ? $query['return'] : null);
    if (!empty($return) && substr($return, 0, 1) != '/') $return = null;
    if (!empty($return)) $return = Common::relativeUrlToAbsolute($return);
    $model->return = $return;

    $model->_responseCode = 200;
    if ($router->getRequestMethod() == 'POST') {
      $this->processLogin($model);
    }

    $view->render($model);
    return $model;
  }

  protected function processLogin(AuthModel &$model) {
    if (empty($model->email)) {
      $model->feedback['email'] = 'Email cannot be empty.';
      return;
    }

    if (!filter_var($model->email, FILTER_VALIDATE_EMAIL)) {
      $model->feedback['email'] = 'Invalid email address.';
      return;
    }

    $user = User::getByEmail($model->email);
    if (!$user) {
      $model->feedback['email'] = 'User not found.';
      return;
    }

    $check = $user->checkPassword($model->password);

    if (!($check & User::PASSWORD_CHECK_VERIFIED)) {
      $model->feedback['password'] = 'Incorrect password.';
      return;
    }

    if ($user->isBanned()) {
      $model->feedback['email'] = 'Account is banned.';
      return;
    }

    if ($check & User::PASSWORD_CHECK_UPGRADE) {
      // Upgrade with provided password, it is verified in previous step
      $user->setPasswordHash(User::createPassword($model->password));
      $user->commit();
    }

    Authentication::login($user);
    if (!empty($model->return)) {
      $model->_responseCode = 303;
      header('Location: ' . $model->return);
    }
  }
}
