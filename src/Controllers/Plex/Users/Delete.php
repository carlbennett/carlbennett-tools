<?php

namespace CarlBennett\Tools\Controllers\Plex\Users;

use \CarlBennett\Tools\Libraries\Core\Router;
use \CarlBennett\Tools\Libraries\Plex\User as PlexUser;
use \CarlBennett\Tools\Libraries\Utility\HTTPForm;
use \CarlBennett\Tools\Models\Plex\Users\UserForm as UserFormModel;

class Delete extends \CarlBennett\Tools\Controllers\Base
{
  public function __construct()
  {
    $this->model = new UserFormModel();
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
      try
      {
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
      $this->model->error = $this->post();
      if ($this->model->error === UserFormModel::ERROR_SUCCESS)
      {
        $this->model->_responseCode = 303;
        $this->model->_responseHeaders['Location'] = \CarlBennett\Tools\Libraries\Core\UrlFormatter::format(
          \sprintf('/plex/users?id=%s&hl=delete', \rawurlencode($this->model->id))
        );
        return true;
      }
    }

    $this->model->_responseCode = 200;
    return true;
  }

  protected function post(): string
  {
    $plex_user = $this->model->plex_user;
    if (!$plex_user) return UserFormModel::ERROR_NULL_PLEX_USER;
    if (\is_string($this->model->user_id) && empty($this->model->user_id)) $this->model->user_id = null;
    return $plex_user->deallocate() ? UserFormModel::ERROR_SUCCESS : UserFormModel::ERROR_INTERNAL_ERROR;
  }
}
