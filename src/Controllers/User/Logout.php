<?php

namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\Tools\Libraries\Core\HttpCode;

class Logout extends \CarlBennett\Tools\Controllers\Base
{
    public function __construct()
    {
        $this->model = new \CarlBennett\Tools\Models\User\Authentication();
    }

    public function invoke(?array $args): bool
    {
        $this->model->feedback = [];

        $q = \CarlBennett\Tools\Libraries\Core\Router::query();
        $return = $q['return'] ?? null;
        if (!empty($return) && substr($return, 0, 1) != '/') $return = null;
        if (!empty($return)) $return = \CarlBennett\Tools\Libraries\Core\UrlFormatter::format($return);
        $this->model->return = $return;

        $this->model->_responseCode = HttpCode::HTTP_OK;
        $this->processLogout();

        return true;
    }

    protected function processLogout(): void
    {
        if (!\CarlBennett\Tools\Libraries\Core\Authentication::logout())
        {
            $this->model->error = 'An error occurred while processing the logout.';
            return;
        }

        $this->model->error = 'Successfully logged out.';

        if (!empty($this->model->return))
        {
            $this->model->_responseCode = HttpCode::HTTP_SEE_OTHER;
            $this->model->_responseHeaders['Location'] = $this->model->return;
        }
    }
}
