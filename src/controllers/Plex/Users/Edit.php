<?php

namespace CarlBennett\Tools\Controllers\Plex\Users;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\Plex\User as PlexUser;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Libraries\Utility\HTTPForm;
use \CarlBennett\Tools\Models\Plex\Users\UserForm as UserFormModel;

use \DateTime;
use \DateTimeZone;
use \Exception;

class Edit extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UserFormModel();
    $model->user = Authentication::$user;

    $form = $router->getRequestBodyArray();
    $query = $router->getRequestQueryArray();

    $form = new HTTPForm($form);
    $query = new HTTPForm($query);

    if (!$model->user ||
      !($model->user->getOptionsBitmask() & User::OPTION_ACL_PLEX_USERS)) {
      $view->render($model);
      $model->_responseCode = 401;
      return $model;
    }

    $model->id = $query->get('id');
    if (!empty($model->id)) {
      try {
        $model->plex_user = new PlexUser($model->id);
      } catch (Exception $e) {
        $model->plex_user = null;
      }
    }

    if (!$model->plex_user) {
      $view->render($model);
      $model->_responseCode = 404;
      return $model;
    }

    $model->action = $form->get('action');
    $model->email = $form->get('email', $model->plex_user->getEmail());
    $model->notes = $form->get('notes', $model->plex_user->getNotes());
    $model->risk = $form->get('risk', $model->plex_user->getRisk());
    $model->username = $form->get('username', $model->plex_user->getUsername());

    if ($router->getRequestMethod() == 'POST') {
      $this->post($model, $form);
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function post($model, $form) {
    $plex_user = $model->plex_user;
    if (!$plex_user) return;

    $plex_user->setUsername($model->username);
    $plex_user->setEmail($model->email);
    $plex_user->setRisk($model->risk);
    $plex_user->setNotes($model->notes);

    if ($model->action == 'Delete') {
      $plex_user->setDateRemoved(
        new DateTime('now', new DateTimeZone('Etc/UTC'))
      );
    } else {
      $plex_user->setDateRemoved(null);
    }

    $plex_user->commit();
  }
}
