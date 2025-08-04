<?php

namespace CarlBennett\Tools\Controllers;

class RedirectSoft extends Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\RedirectSoft();
  }

  public function invoke(?array $args): bool
  {
    if (\is_null($args) || \count($args) != 1) throw new \InvalidArgumentException();

    $this->model->location = \CarlBennett\Tools\Libraries\Core\UrlFormatter::format(\array_shift($args));
    if (!empty($this->model->location)) $this->model->_responseHeaders['Location'] = $this->model->location;

    $this->model->_responseCode = 302;
    return true;
  }
}
