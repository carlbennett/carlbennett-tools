<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\User\Invite as Invitation;
use \CarlBennett\Tools\Models\User\Invite as InviteModel;
use \InvalidArgumentException;
use \UnexpectedValueException;

class Invite extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new InviteModel();
    $model->auth_user = Authentication::$user;
    $model->feedback = array(); // for bootstrap field/color
    $model->_responseCode = 200;

    if ($model->auth_user) {
      $model->invites_available = $model->auth_user->getInvitesAvailable();
      $model->invites_used = $model->auth_user->getInvitesUsed();
    }

    $query = $router->getRequestQueryArray();

    $return = $query['return'] ?? null;
    if (!empty($return) && substr($return, 0, 1) != '/') $return = null;
    if (!empty($return)) $return = Common::relativeUrlToAbsolute($return);
    $model->return = $return;

    $model->id = $query['id'] ?? null;
    if (!empty($model->id)) {
      $this->lookupInvite($router, $model);
    }

    if ($router->getRequestMethod() == 'POST') {
      $this->processInvite($router, $model);
    }

    if (!empty($model->return)) {
      $model->_responseCode = 303;
      header('Location: ' . $model->return);
    }

    $view->render($model);
    return $model;
  }

  protected function lookupInvite(Router &$router, InviteModel &$model) {
    try {
      $invite = new Invitation($model->id);
    } catch (InvalidArgumentException $e) {
      // user inputted id value was malformed
      $invite = null;
    } catch (UnexpectedValueException $e) {
      // user inputted id value was not found (so object cannot be created)
      $invite = null;
    }

    if (!$invite) return;

    $model->date_accepted = $invite->getDateAccepted();
    $model->date_invited = $invite->getDateInvited();
    $model->date_revoked = $invite->getDateRevoked();
    $model->email = $invite->getEmail();
    $model->id = $invite->getId();
    $model->invited_by = $invite->getInvitedBy();
    $model->invited_user = $invite->getInvitedUser();
    $model->record_updated = $invite->getRecordUpdated();
  }

  protected function processInvite(Router &$router, InviteModel &$model) {
    $data = $router->getRequestBodyArray();
    $model->email = $data['email'] ?? null;
    $model->feedback = array('email', 'Invalid email address.');
    // TODO
  }
}
