<?php

namespace CarlBennett\Tools\Views;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\RemoteAddress as RemoteAddressModel;

class RemoteAddressPlain extends View {
  public function getMimeType() {
    return 'text/plain;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof RemoteAddressModel) {
      throw new IncorrectModelException();
    }
    echo $model->ip_address;
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
