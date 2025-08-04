<?php

namespace CarlBennett\Tools\Controllers\Plex\Users;

use \CarlBennett\Tools\Libraries\Core\Router;
use \CarlBennett\Tools\Libraries\Plex\User as PlexUser;
use \CarlBennett\Tools\Libraries\Utility\HTTPForm;
use \CarlBennett\Tools\Models\Plex\Users\UserForm as UserFormModel;

class Edit extends \CarlBennett\Tools\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\Plex\Users\UserForm();
  }

  public function invoke(?array $args): bool
  {
    if (!\is_null($args) && \count($args) > 0) throw new \InvalidArgumentException();

    $form = new HTTPForm(Router::query());

    if (!$this->model->active_user
      || !$this->model->active_user->getAclObject()->getAcl(\CarlBennett\Tools\Libraries\User\Acl::ACL_PLEX_USERS))
    {
      $this->model->_responseCode = 401;
      return true;
    }

    $this->model->id = $form->get('id');
    if (!empty($this->model->id))
    {
      try {
        $this->model->plex_user = new PlexUser($this->model->id);
      }
      catch (\Throwable)
      {
        $this->model->plex_user = null;
      }
    }

    if (!$this->model->plex_user)
    {
      $this->model->_responseCode = 404;
      return true;
    }

    $this->model->disabled = $form->get('disabled', $this->model->plex_user->isDisabled());
    $this->model->expired = $form->get('expired', $this->model->plex_user->isExpired());
    $this->model->hidden = $form->get('hidden', $this->model->plex_user->isHidden());
    $this->model->homeuser = $form->get('homeuser', $this->model->plex_user->isHomeUser());
    $this->model->notes = $form->get('notes', $this->model->plex_user->getNotes());
    $this->model->plex_email = $form->get('plex_email', $this->model->plex_user->getPlexEmail());
    $this->model->plex_thumb = $form->get('plex_thumb', $this->model->plex_user->getPlexThumb());
    $this->model->plex_title = $form->get('plex_title', $this->model->plex_user->getPlexTitle());
    $this->model->plex_username = $form->get('plex_username', $this->model->plex_user->getPlexUsername());
    $this->model->risk = $form->get('risk', $this->model->plex_user->getRisk());
    $this->model->user_id = $form->get('user_id', $this->model->plex_user->getUserId());

    if (Router::requestMethod() == Router::METHOD_POST)
    {
      $this->model->error = $this->post($form);
      if ($this->model->error === UserFormModel::ERROR_SUCCESS)
      {
        $this->model->_responseCode = 303;
        $this->model->_responseHeaders['Location'] = \CarlBennett\Tools\Libraries\Core\UrlFormatter::format(
          \sprintf('/plex/users?id=%s&hl=edit', \rawurlencode($this->model->id))
        );
        return true;
      }
    }

    $this->model->_responseCode = 200;
    return true;
  }

  protected function post(HTTPForm $form): string
  {
    $plex_user = $this->model->plex_user;
    if (!$plex_user)
      return UserFormModel::ERROR_NULL_PLEX_USER;

    if (empty($this->model->plex_title) && empty($this->model->plex_username) && empty($this->model->plex_email))
      return UserFormModel::ERROR_EMPTY_TITLE_USERNAME_AND_EMAIL;

    if ($this->model->risk < 0 || $this->model->risk > 3)
      return UserFormModel::ERROR_INVALID_RISK;

    if (is_string($this->model->user_id) && empty($this->model->user_id))
      $this->model->user_id = null;

    // Re-evaluate checkboxes not present in sent form
    $this->model->disabled = $form->get('disabled', false);
    $this->model->expired = $form->get('expired', false);
    $this->model->hidden = $form->get('hidden', false);
    $this->model->homeuser = $form->get('homeuser', false);

    $plex_user->setNotes($this->model->notes);
    $plex_user->setPlexEmail($this->model->plex_email);
    $plex_user->setPlexThumb($this->model->plex_thumb);
    $plex_user->setPlexTitle($this->model->plex_title);
    $plex_user->setPlexUsername($this->model->plex_username);
    $plex_user->setRecordUpdated('now');
    $plex_user->setRisk($this->model->risk);
    $plex_user->setUserId($this->model->user_id);

    if (!$plex_user->isDisabled() && $this->model->disabled)
    {
      $plex_user->setOption(PlexUser::OPTION_DISABLED, true);
      $plex_user->setDateDisabled('now');
    }
    else if ($plex_user->isDisabled() && !$this->model->disabled)
    {
      $plex_user->setOption(PlexUser::OPTION_DISABLED, false);
      $plex_user->setDateDisabled(null);
    }

    if (!$plex_user->isExpired() && $this->model->expired)
      $plex_user->setDateExpired('now');
    else if ($plex_user->isExpired() && !$this->model->expired)
      $plex_user->setDateExpired(null);

    if (!$plex_user->isHidden() && $this->model->hidden)
      $plex_user->setOption(PlexUser::OPTION_HIDDEN, true);
    else if ($plex_user->isHidden() && !$this->model->hidden)
      $plex_user->setOption(PlexUser::OPTION_HIDDEN, false);

    if (!$plex_user->isHomeUser() && $this->model->homeuser)
      $plex_user->setOption(PlexUser::OPTION_HOMEUSER, true);
    else if ($plex_user->isHomeUser() && !$this->model->homeuser)
      $plex_user->setOption(PlexUser::OPTION_HOMEUSER, false);

    try
    {
      if (!$plex_user->commit()) return UserFormModel::ERROR_INTERNAL_ERROR;
    }
    catch (\PDOException $e)
    {
      if (\strpos($e->getMessage(), 'Duplicate entry') !== false
        && \strpos($e->getMessage(), 'for key \'idx_user_id\'') !== false)
      {
        return UserFormModel::ERROR_LINKED_USER_ALREADY_ASSIGNED;
      } else throw $e;
    }

    return UserFormModel::ERROR_SUCCESS;
  }
}
