<?php

namespace CarlBennett\Tools\Controllers;

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
    $this->model->_responseCode = \phpinfo(\INFO_ALL) ? 200 : 500;
    $this->model->phpinfo = \ob_get_clean();

    return true;
  }
}
