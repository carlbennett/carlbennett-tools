<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\Tools\Libraries\Core\HttpCode;

class Users extends Base
{
    public function __construct()
    {
        $this->model = new \CarlBennett\Tools\Models\Users();
    }

    public function invoke(?array $args): bool
    {
        if (!$this->model->active_user || !$this->model->active_user->getAclObject()->getAcl(\CarlBennett\Tools\Libraries\User\Acl::ACL_USERS_MANAGE))
        {
            $this->model->_responseCode = HttpCode::HTTP_UNAUTHORIZED;
            return true;
        }

        $q = new \CarlBennett\Tools\Libraries\Utility\HTTPForm(\CarlBennett\Tools\Libraries\Core\Router::query());

        $this->model->hl = $q->get('hl');
        $this->model->id = $q->get('id');
        $this->model->show_hidden = (bool) $q->get('sh');
        $this->model->users = \CarlBennett\Tools\Libraries\User\User::getAll();

        $this->model->_responseCode = HttpCode::HTTP_OK;
        return true;
    }
}
