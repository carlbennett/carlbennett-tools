<?php

namespace CarlBennett\Tools\Controllers\User;

class Logout extends \CarlBennett\Tools\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\User\Authentication();
  }

  public function invoke(?array $args): bool
  {
    $this->model->feedback = [];

    $q = \CarlBennett\Tools\Libraries\Router::query();
    $return = $q['return'] ?? null;
    if (!empty($return) && substr($return, 0, 1) != '/') $return = null;
    if (!empty($return)) $return = \CarlBennett\MVC\Libraries\Common::relativeUrlToAbsolute($return);
    $this->model->return = $return;

    $this->model->_responseCode = 200;
    $this->processLogout();

    return true;
  }

  protected function processLogout(): void
  {
    if (!\CarlBennett\Tools\Libraries\Authentication::logout())
    {
      $this->model->feedback = 'An error occurred while processing the logout.';
      return;
    }

    $this->model->feedback = 'Successfully logged out.';

    if (!empty($this->model->return))
    {
      $this->model->_responseCode = 303;
      $this->model->_responseHeaders['Location'] = $this->model->return;
    }
  }
}
