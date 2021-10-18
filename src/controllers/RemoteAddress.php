<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\GeoIP;
use \CarlBennett\Tools\Models\RemoteAddress as RemoteAddressModel;

class RemoteAddress extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new RemoteAddressModel();
    $model->ip_address = getenv('REMOTE_ADDR');
    $model->geoip_info = GeoIP::getRecord($model->ip_address);
    $model->user_agent = getenv('HTTP_USER_AGENT');
    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
