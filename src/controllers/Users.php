<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Libraries\User\Acl;
use \CarlBennett\Tools\Libraries\Utility\HTTPForm;
use \CarlBennett\Tools\Models\Users as UsersModel;

class Users extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UsersModel();
    $model->active_user = Authentication::$user;

    if (!$model->active_user
      || !$model->active_user->getAclObject()->getAcl(Acl::ACL_USERS_MANAGE)) {
      $model->users = false;
    } else {
      $model->users = User::getAll();
    }

    $query = $router->getRequestQueryArray();
    $query = new HTTPForm($query);

    $model->show_hidden = $query->get('sh');
    $model->id = $query->get('id');
    $model->hl = $query->get('hl');

    $view->render($model);
    $model->_responseCode = ($model->users ? 200 : 401);
    return $model;
  }
}
