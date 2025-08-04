<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\Tools\Libraries\Core\Router;

class UrlEncodeDecode extends Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\UrlEncodeDecode();
  }

  public function invoke(?array $args): bool
  {
    if (Router::requestMethod() == Router::METHOD_POST)
    {
      $q = Router::query();
      $this->model->decode = ($q['decode'] ?? null) ? true : false;
      $this->model->input = $q['input'] ?? null;

      if (!empty($this->model->input))
      {
        $this->model->output = $this->model->decode ?
          \rawurldecode($this->model->input) : \rawurlencode($this->model->input);
      }
    }

    $this->model->_responseCode = 200;
    return true;
  }
}
