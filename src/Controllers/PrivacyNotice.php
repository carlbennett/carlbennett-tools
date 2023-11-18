<?php

namespace CarlBennett\Tools\Controllers;

class PrivacyNotice extends Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\PrivacyNotice();
  }

  public function invoke(?array $args): bool
  {
    if (!\is_null($args) && \count($args) > 0) throw new \InvalidArgumentException();

    $privacy = \CarlBennett\MVC\Libraries\Common::$config->privacy;
    $this->model->data_location = $privacy->data_location;
    $this->model->email_domain = $privacy->contact->email_domain;
    $this->model->org = $privacy->contact->org;
    $this->model->web_domain = getenv('HTTP_HOST') ?? '';

    $this->model->_responseCode = 200;
    return true;
  }
}
