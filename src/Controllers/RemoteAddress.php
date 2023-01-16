<?php

namespace CarlBennett\Tools\Controllers;

class RemoteAddress extends \CarlBennett\Tools\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\RemoteAddress();
  }

  public function invoke(?array $args): bool
  {
    if (!\is_null($args) && \count($args) > 0) throw new \InvalidArgumentException();

    $this->model->ip_address = \getenv('REMOTE_ADDR');
    $this->model->geoip_info = \CarlBennett\Tools\Libraries\GeoIP::getRecord($this->model->ip_address);
    $this->model->user_agent = \getenv('HTTP_USER_AGENT');
    $this->model->_responseCode = 200;
    return true;
  }
}
