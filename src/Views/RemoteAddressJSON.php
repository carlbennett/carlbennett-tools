<?php

namespace CarlBennett\Tools\Views;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\RemoteAddress as RemoteAddressModel;

class RemoteAddressJSON extends View {
  const MAX_USER_AGENT = 0xFFFF; // prevents buffer overflow from user input

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
      'user_agent' => substr($model->user_agent, 0, self::MAX_USER_AGENT),
    ), Common::prettyJSONIfBrowser());
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
