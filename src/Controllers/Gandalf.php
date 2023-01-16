<?php

namespace CarlBennett\Tools\Controllers;

class Gandalf extends Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\Gandalf();
  }

  public function invoke(?array $args): bool
  {
    if (!\is_null($args) && \count($args) > 0) throw new \InvalidArgumentException();

    $this->model->_responseCode = 200;
    return true;
  }
}
