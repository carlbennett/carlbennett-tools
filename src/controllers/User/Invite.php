<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Models\User\Invite as InviteModel;

class Invite extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new InviteModel();
    $model->feedback = array();

    $query = $router->getRequestQueryArray();
    $return = (isset($query['return']) ? $query['return'] : null);
    if (!empty($return) && substr($return, 0, 1) != '/') $return = null;
    if (!empty($return)) $return = Common::relativeUrlToAbsolute($return);
    $model->return = $return;

    $model->_responseCode = 500;
    $this->processInvite($model);

    $view->render($model);
    return $model;
  }

  protected function processInvite(InviteModel &$model) {
    $model->feedback = 'An error occurred while processing the request.';
    return;

    if (!empty($model->return)) {
      $model->_responseCode = 303;
      header('Location: ' . $model->return);
    }
  }
}
