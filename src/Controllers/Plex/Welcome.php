<?php

namespace CarlBennett\Tools\Controllers\Plex;

class Welcome extends \CarlBennett\Tools\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\Plex\Welcome();
  }

  public function invoke(?array $args): bool
  {
    if (!\is_null($args) && \count($args) > 0) throw new \InvalidArgumentException();

    $this->model->_responseCode = 200;
    return true;
  }
}
