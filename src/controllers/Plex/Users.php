<?php

namespace CarlBennett\Tools\Controllers\Plex;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\Plex\User as PlexUser;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Libraries\Utility\HTTPForm;
use \CarlBennett\Tools\Models\Plex\Users as UsersModel;

class Users extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UsersModel();
    $model->active_user = Authentication::$user;

    if (!$model->active_user
      || !$model->active_user->getOption(User::OPTION_ACL_PLEX_USERS)) {
      $model->users = false;
    } else {
      $model->users = PlexUser::getAll();
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
