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
use \CarlBennett\Tools\Libraries\User\Acl;
use \CarlBennett\Tools\Libraries\Utility\HTTPForm;
use \CarlBennett\Tools\Models\Plex\Users\UserForm as UserFormModel;

use \DateTimeZone;
use \Exception;
use \PDOException;

class Delete extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UserFormModel();
    $model->active_user = Authentication::$user;

    $form = $router->getRequestBodyArray();
    $query = $router->getRequestQueryArray();

    $form = new HTTPForm($form);
    $query = new HTTPForm($query);

    if (!($model->active_user &&
      $model->active_user->getAclObject()->getAcl(Acl::ACL_PLEX_USERS)
    )) {
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

    $model->disabled = $form->get('disabled', $model->plex_user->isDisabled());
    $model->expired = $form->get('expired', $model->plex_user->isExpired());
    $model->hidden = $form->get('hidden', $model->plex_user->isHidden());
    $model->homeuser = $form->get('homeuser', $model->plex_user->isHomeUser());
    $model->notes = $form->get('notes', $model->plex_user->getNotes());
    $model->plex_email = $form->get(
      'plex_email', $model->plex_user->getPlexEmail()
    );
    $model->plex_thumb = $form->get(
      'plex_thumb', $model->plex_user->getPlexThumb()
    );
    $model->plex_title = $form->get(
      'plex_title', $model->plex_user->getPlexTitle()
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
          sprintf('/plex/users?id=%s&hl=delete', rawurlencode($model->id))
        );
        return $model;
      }
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function post($model, $form): mixed
  {
    $plex_user = $model->plex_user;
    if (!$plex_user) return UserFormModel::ERROR_NULL_PLEX_USER;
    if (is_string($model->user_id) && empty($model->user_id)) $model->user_id = null;
    return $plex_user->deallocate() ? UserFormModel::ERROR_SUCCESS : UserFormModel::ERROR_INTERNAL_ERROR;
  }
}
