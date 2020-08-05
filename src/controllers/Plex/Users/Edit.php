<?php

namespace CarlBennett\Tools\Controllers\Plex\Users;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DateTime;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\Plex\User as PlexUser;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Libraries\Utility\HTTPForm;
use \CarlBennett\Tools\Models\Plex\Users\UserForm as UserFormModel;

use \DateTimeZone;
use \Exception;

class Edit extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UserFormModel();
    $model->active_user = Authentication::$user;

    $form = $router->getRequestBodyArray();
    $query = $router->getRequestQueryArray();

    $form = new HTTPForm($form);
    $query = new HTTPForm($query);

    if (!($model->active_user && (
      $model->active_user->getOptionsBitmask() & User::OPTION_ACL_PLEX_USERS
    ))) {
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
    $model->notes = $form->get('notes', $model->plex_user->getNotes());
    $model->plex_email = $form->get(
      'plex_email', $model->plex_user->getPlexEmail()
    );
    $model->plex_username = $form->get(
      'plex_username', $model->plex_user->getPlexUsername()
    );
    $model->risk = $form->get('risk', $model->plex_user->getRisk());
    $model->user_id = $form->get('user_id', $model->plex_user->getUserId());

    if ($router->getRequestMethod() == 'POST') {
      $model->error = $this->post($model, $form);
      if ($model->error === UserFormModel::ERROR_SUCCESS) {
        $view->render($model);
        $model->_responseCode = 303;
        $model->_responseHeaders['Location'] = Common::relativeUrlToAbsolute(
          sprintf('/plex/users?id=%s&hl=edit', rawurlencode($model->id))
        );
        return $model;
      }
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function post($model, $form) {
    $plex_user = $model->plex_user;
    if (!$plex_user)
      return UserFormModel::ERROR_NULL_PLEX_USER;

    if (empty($model->plex_username) && empty($model->plex_email))
      return UserFormModel::ERROR_EMPTY_USERNAME_AND_EMAIL;

    if ($model->risk < 0 || $model->risk > 3)
      return UserFormModel::ERROR_INVALID_RISK;

    if (is_string($model->user_id) && empty($model->user_id))
      $model->user_id = null;

    $plex_user->setNotes($model->notes);
    $plex_user->setPlexEmail($model->plex_email);
    $plex_user->setPlexUsername($model->plex_username);
    $plex_user->setRisk($model->risk);
    $plex_user->setUserId($model->user_id);

    if ($model->action == 'Disable') {
      $plex_user->setDateDisabled(
        new DateTime('now', new DateTimeZone('Etc/UTC'))
      );
    } else {
      $plex_user->setDateDisabled(null);
    }

    if (!$plex_user->commit())
      return UserFormModel::ERROR_INTERNAL_ERROR;

    return UserFormModel::ERROR_SUCCESS;
  }
}
