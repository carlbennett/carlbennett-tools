<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\Tools\Libraries\Core\Router;
use \CarlBennett\Tools\Libraries\User\User;

class Login extends \CarlBennett\Tools\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\User\Authentication();
  }

  public function invoke(?array $args): bool
  {
    $this->model->feedback = [];

    $q = Router::query();
    $this->model->email = $q['email'] ?? null;
    $this->model->password = $q['password'] ?? null;

    $return = $q['return'] ?? null;
    if (!empty($return) && substr($return, 0, 1) != '/') $return = null;
    if (!empty($return)) $return = \CarlBennett\Tools\Libraries\Core\UrlFormatter::format($return);
    $this->model->return = $return;

    $this->model->_responseCode = 200;
    if (Router::requestMethod() == Router::METHOD_POST) $this->processLogin();

    return true;
  }

  protected function processLogin(): void
  {
    $model = $this->model;

    if (empty($model->email))
    {
      $model->feedback['email'] = 'Email cannot be empty.';
      return;
    }

    if (!filter_var($model->email, FILTER_VALIDATE_EMAIL))
    {
      $model->feedback['email'] = 'Invalid email address.';
      return;
    }

    $user = User::getByEmail($model->email);

    if (!$user)
    {
      $model->feedback['email'] = 'User not found.';
      return;
    }

    $check = $user->checkPassword($model->password);

    if (!($check & User::PASSWORD_CHECK_VERIFIED))
    {
      $model->feedback['password'] = 'Incorrect password.';
      return;
    }

    if ($user->isBanned())
    {
      $model->feedback['email'] = 'Account is banned.';
      return;
    }

    if ($check & User::PASSWORD_CHECK_UPGRADE)
    {
      // Upgrade with provided password, it is verified in previous step
      $user->setPasswordHash(User::createPassword($model->password));
      $user->commit();
    }

    \CarlBennett\Tools\Libraries\Core\Authentication::login($user);

    if (!empty($model->return))
    {
      $model->_responseCode = 303;
      $model->_responseHeaders['Location'] = $model->return;
    }
  }
}
