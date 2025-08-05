<?php

namespace CarlBennett\Tools\Controllers;

class Tools extends Base
{
    public function __construct()
    {
        $this->model = new \CarlBennett\Tools\Models\Tools();
    }

    public function invoke(?array $args): bool
    {
        if (!\is_null($args) && \count($args) > 0) throw new \InvalidArgumentException();

        $this->model->routes = \CarlBennett\Tools\Libraries\Core\Router::$routes;
        $this->model->_responseCode = \CarlBennett\Tools\Libraries\Core\HttpCode::HTTP_OK;
        return true;
    }
}
