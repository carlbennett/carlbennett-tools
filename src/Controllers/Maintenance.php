<?php

namespace CarlBennett\Tools\Controllers;

class Maintenance extends Base
{
    public function __construct()
    {
        $this->model = new \CarlBennett\Tools\Models\Maintenance();
    }

    public function invoke(?array $args): bool
    {
        if (\is_null($args) || \count($args) < 1) throw new \InvalidArgumentException();

        $this->model->_responseCode = \CarlBennett\Tools\Libraries\Core\HttpCode::HTTP_SERVICE_UNAVAILABLE;
        $this->model->message = \array_shift($args) ?? '';
        return true;
    }
}
