<?php

namespace CarlBennett\Tools\Views;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\RemoteAddress as RemoteAddressModel;

class RemoteAddressJSON extends View {
  public function getMimeType() {
    return 'application/json;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof RemoteAddressModel) {
      throw new IncorrectModelException();
    }
    echo json_encode(array(
      'ip_address' => $model->ip_address,
      'geoip_info' => $model->geoip_info,
    ), Common::prettyJSONIfBrowser());
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
