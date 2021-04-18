<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\RemoteAddress as RemoteAddressModel;

class RemoteAddress extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new RemoteAddressModel();
    $model->ip_address = getenv('REMOTE_ADDR');
    if (extension_loaded('geoip')) {
      $model->geoip_info = geoip_record_by_name($model->ip_address);
    } else {
      $model->geoip_info = null;
    }
    $model->user_agent = getenv('HTTP_USER_AGENT');
    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
