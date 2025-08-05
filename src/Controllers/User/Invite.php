<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\Tools\Libraries\Core\HttpCode;
use \CarlBennett\Tools\Libraries\Core\Router;
use \CarlBennett\Tools\Libraries\User\Invite as Invitation;
use \CarlBennett\Tools\Libraries\User\User;
use \CarlBennett\Tools\Models\User\Invite as InviteModel;
use \InvalidArgumentException;
use \Throwable;
use \UnexpectedValueException;

class Invite extends \CarlBennett\Tools\Controllers\Base
{
    public function __construct()
    {
        $this->model = new \CarlBennett\Tools\Models\User\Invite();
    }

    public function invoke(?array $args): bool
    {
        $this->model->feedback = []; // for bootstrap field/color
        $this->model->_responseCode = HttpCode::HTTP_OK;

        $q = Router::query();

        $return = $q['return'] ?? null;
        if (!empty($return) && substr($return, 0, 1) != '/') $return = null;
        if (!empty($return)) $return = \CarlBennett\Tools\Libraries\Core\UrlFormatter::format($return);
        $this->model->return = $return;

        $this->model->id = $q['id'] ?? null;

        if (!empty($this->model->id)) $this->lookupInvite();
        if (Router::requestMethod() == Router::METHOD_POST) $this->processInvite();

        if ($this->model->active_user)
        {
            $this->model->invites_available = $this->model->active_user->getInvitesAvailable();
            $this->model->invites_sent = $this->model->active_user->getInvitesSent();
            $this->model->invites_used = $this->model->active_user->getInvitesUsed();
        }

        if (!empty($this->model->return))
        {
            $this->model->_responseCode = HttpCode::HTTP_SEE_OTHER;
            $this->model->_responseHeaders['Location'] = $this->model->return;
        }

        return true;
    }

    protected function lookupInvite(): void
    {
        try
        {
            $this->model->error = InviteModel::ERROR_INTERNAL_ERROR;
            $invite = new Invitation($this->model->id);
        }
        catch (InvalidArgumentException)
        {
            // user inputted id value was malformed
            $this->model->error = InviteModel::ERROR_ID_MALFORMED;
            $invite = null;
        }
        catch (UnexpectedValueException)
        {
            // user inputted id value was not found (so object cannot be created)
            $this->model->error = InviteModel::ERROR_ID_NOT_FOUND;
            $invite = null;
        }

        if (!$invite) return;

        $this->model->date_accepted = $invite->getDateAccepted();
        $this->model->date_invited = $invite->getDateInvited();
        $this->model->date_revoked = $invite->getDateRevoked();
        $this->model->email = $invite->getEmail();
        $this->model->error = InviteModel::ERROR_SUCCESS;
        $this->model->id = $invite->getId();
        $this->model->invited_by = $invite->getInvitedBy();
        $this->model->invited_user = $invite->getInvitedUser();
        $this->model->record_updated = $invite->getRecordUpdated();
    }

    protected function processInvite(): void
    {
        $data = Router::query();
        $now = new \DateTimeImmutable('now');
        $this->model->email = $data['email'] ?? null;

        // user input must be valid email address
        if (!filter_var($this->model->email, FILTER_VALIDATE_EMAIL))
        {
            $this->model->error = InviteModel::ERROR_EMAIL_INVALID;
            $this->model->feedback = ['email', 'danger'];
            return;
        }

        // find current invite
        $invite = Invitation::getByEmail($this->model->email);
        if (!is_null($invite) && !is_null($invite->getInvitedBy()) &&
            $invite->getInvitedBy()->getId() !== $this->model->active_user->getId())
        {
            $this->model->error = InviteModel::ERROR_EMAIL_ALREADY_INVITED;
            $this->model->feedback = ['email', 'danger'];
            return;
        }

        // no current invite, create one?
        if (is_null($invite))
        {
            $invites_available = $this->model->active_user->getInvitesAvailable();

            // check for invites available
            if ($invites_available < 1)
            {
                $this->model->error = InviteModel::ERROR_INVITES_AVAILABLE_ZERO;
                return;
            }

            // check if account already exists
            $user = User::getByEmail($this->model->email);
            if ($user instanceof User)
            {
                $this->model->error = InviteModel::ERROR_EMAIL_ALREADY_INVITED;
                $this->model->feedback = ['email', 'danger'];
                return;
            }

            // remove one available invite
            $this->model->active_user->setInvitesAvailable($invites_available - 1);
            try
            {
                $this->model->active_user->commit();
            }
            catch (Throwable)
            {
                $this->model->error = InviteModel::ERROR_INTERNAL_ERROR;
                $this->model->feedback = ['email', 'danger'];
                return;
            }

            // create invite
            $invite = new Invitation(null);
        }

        if (\is_null($invite->getDateInvited())) $invite->setDateInvited($now);
        if (\is_null($invite->getInvitedBy())) $invite->setInvitedBy($this->model->active_user);

        $invite->setEmail($this->model->email); // update for any case corrections
        $invite->setRecordUpdated($now);

        try
        {
            $invite->commit();
            $this->model->error = InviteModel::ERROR_SUCCESS;
            $this->model->feedback = ['email', 'success'];
        }
        catch (Throwable)
        {
            $this->model->error = InviteModel::ERROR_INTERNAL_ERROR;
            $this->model->feedback = ['email', 'danger'];
        }
    }
}
