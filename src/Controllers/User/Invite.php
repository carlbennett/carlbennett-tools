<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Libraries\User\Invite as Invitation;
use \CarlBennett\Tools\Models\User\Invite as InviteModel;
use \DateTime;
use \Exception;
use \InvalidArgumentException;
use \UnexpectedValueException;

class Invite extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new InviteModel();
    $model->active_user = Authentication::$user;
    $model->feedback = array(); // for bootstrap field/color
    $model->_responseCode = 200;

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

    if ($model->active_user) {
      $model->invites_available = $model->active_user->getInvitesAvailable();
      $model->invites_sent = $model->active_user->getInvitesSent();
      $model->invites_used = $model->active_user->getInvitesUsed();
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
      $model->error = InviteModel::ERROR_INTERNAL_ERROR;
      $invite = new Invitation($model->id);
    } catch (InvalidArgumentException $e) {
      // user inputted id value was malformed
      $model->error = InviteModel::ERROR_ID_MALFORMED;
      $invite = null;
    } catch (UnexpectedValueException $e) {
      // user inputted id value was not found (so object cannot be created)
      $model->error = InviteModel::ERROR_ID_NOT_FOUND;
      $invite = null;
    }

    if (!$invite) return;

    $model->date_accepted = $invite->getDateAccepted();
    $model->date_invited = $invite->getDateInvited();
    $model->date_revoked = $invite->getDateRevoked();
    $model->email = $invite->getEmail();
    $model->error = InviteModel::ERROR_SUCCESS;
    $model->id = $invite->getId();
    $model->invited_by = $invite->getInvitedBy();
    $model->invited_user = $invite->getInvitedUser();
    $model->record_updated = $invite->getRecordUpdated();
  }

  protected function processInvite(Router &$router, InviteModel &$model) {
    $data = $router->getRequestBodyArray();
    $now = new DateTime('now');
    $model->email = $data['email'] ?? null;

    // user input must be valid email address
    if (!filter_var($model->email, FILTER_VALIDATE_EMAIL)) {
      $model->error = InviteModel::ERROR_EMAIL_INVALID;
      $model->feedback = array('email', 'danger');
      return;
    }

    // find current invite
    $invite = Invitation::getByEmail($model->email);
    if (!is_null($invite) && !is_null($invite->getInvitedBy())
      && $invite->getInvitedBy()->getId() !== $model->active_user->getId()) {
      $model->error = InviteModel::ERROR_EMAIL_ALREADY_INVITED;
      $model->feedback = array('email', 'danger');
      return;
    }

    // no current invite, create one?
    if (is_null($invite)) {
      $invites_available = $model->active_user->getInvitesAvailable();

      // check for invites available
      if ($invites_available < 1) {
        $model->error = InviteModel::ERROR_INVITES_AVAILABLE_ZERO;
        return;
      }

      // check if account already exists
      $user = User::getByEmail($model->email);
      if ($user instanceof User) {
        $model->error = InviteModel::ERROR_EMAIL_ALREADY_INVITED;
        $model->feedback = array('email', 'danger');
        return;
      }

      // remove one available invite
      $model->active_user->setInvitesAvailable($invites_available - 1);
      try {
        $model->active_user->commit();
      } catch (Exception $e) {
        $model->error = InviteModel::ERROR_INTERNAL_ERROR;
        $model->feedback = array('email', 'danger');
        return;
      }

      // create invite
      $invite = new Invitation(null);
    }

    if (is_null($invite->getDateInvited())) {
      $invite->setDateInvited($now);
    }

    if (is_null($invite->getInvitedBy())) {
      $invite->setInvitedBy($model->active_user);
    }

    $invite->setEmail($model->email); // update for any case corrections
    $invite->setRecordUpdated($now);

    try {
      $invite->commit();
      $model->error = InviteModel::ERROR_SUCCESS;
      $model->feedback = array('email', 'success');
    } catch (Exception $e) {
      $model->error = InviteModel::ERROR_INTERNAL_ERROR;
      $model->feedback = array('email', 'danger');
    }
  }
}
