<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\Tools\Libraries\Core\HttpCode;

class PhpInfo extends Base
{
    public function __construct()
    {
        $this->model = new \CarlBennett\Tools\Models\PhpInfo();
    }

    public function invoke(?array $args): bool
    {
        if (!\is_null($args) && \count($args) > 0) throw new \InvalidArgumentException();

        \ob_start();
        $this->model->_responseCode = \phpinfo(\INFO_ALL) ? HttpCode::HTTP_OK : HttpCode::HTTP_INTERNAL_SERVER_ERROR;
        $this->model->phpinfo = \ob_get_clean();

        return true;
    }
}
