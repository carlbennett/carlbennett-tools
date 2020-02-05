<?php

namespace CarlBennett\Tools\Controllers\Plex\Users;

use \CarlBennett\MVC\Libraries\Common;
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

class Add extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UserFormModel();
    $model->active_user = Authentication::$user;

    $form = $router->getRequestBodyArray();
    $query = $router->getRequestQueryArray();

    if (!($model->active_user && (
      $model->active_user->getOptionsBitmask() & User::OPTION_ACL_PLEX_USERS
    ))) {
      $view->render($model);
      $model->_responseCode = 401;
      return $model;
    }

    $model->plex_user = new PlexUser(null);
    $form = new HTTPForm($form);

    $model->notes = $form->get('notes', $model->plex_user->getNotes());
    $model->plex_email = $form->get(
      'plex_email', $model->plex_user->getPlexEmail()
    );
    $model->plex_username = $form->get(
      'plex_username', $model->plex_user->getPlexUsername()
    );
    $model->risk = $form->get('risk', $model->plex_user->getRisk());

    if ($router->getRequestMethod() == 'POST') {
      $model->error = $this->post($model, $form);
      if ($model->error === UserFormModel::ERROR_SUCCESS) {
        $view->render($model);
        $model->_responseCode = 303;
        $model->_responseHeaders['Location'] = Common::relativeUrlToAbsolute(
          sprintf('/plex/users?id=%s&hl=add', rawurlencode($model->id))
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

    $plex_user->setDateAdded(new DateTime('now', new DateTimeZone('Etc/UTC')));
    $plex_user->setNotes($model->notes);
    $plex_user->setPlexEmail($model->plex_email);
    $plex_user->setPlexUsername($model->plex_username);
    $plex_user->setRisk($model->risk);

    if (!$plex_user->commit())
      return UserFormModel::ERROR_INTERNAL_ERROR;

    return UserFormModel::ERROR_SUCCESS;
  }
}
