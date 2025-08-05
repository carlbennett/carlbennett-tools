<?php

namespace CarlBennett\Tools\Controllers\Plex;

use \CarlBennett\Tools\Libraries\Core\HttpCode;

class Users extends \CarlBennett\Tools\Controllers\Base
{
    public function __construct()
    {
        $this->model = new \CarlBennett\Tools\Models\Plex\Users();
    }

    public function invoke(?array $args): bool
    {
        if (!\is_null($args) && \count($args) > 0) throw new \InvalidArgumentException();

        $this->model->users = !$this->model->active_user ||
            !$this->model->active_user->getAclObject()->getAcl(\CarlBennett\Tools\Libraries\User\Acl::ACL_PLEX_USERS)
            ? false : \CarlBennett\Tools\Libraries\Plex\User::getAll();

        $query = \CarlBennett\Tools\Libraries\Core\Router::query();
        $query = new \CarlBennett\Tools\Libraries\Utility\HTTPForm($query);

        $this->model->show_hidden = $query->get('sh');
        $this->model->id = $query->get('id');
        $this->model->hl = $query->get('hl');

        $this->model->_responseCode = $this->model->users ? HttpCode::HTTP_OK : HttpCode::HTTP_UNAUTHORIZED;
        return true;
    }
}
