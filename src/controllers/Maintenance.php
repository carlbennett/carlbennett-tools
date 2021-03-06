<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\Maintenance as MaintenanceModel;

class Maintenance extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new MaintenanceModel();
    $model->message = array_shift($args);
    $view->render($model);
    $model->_responseCode = 503;
    return $model;
  }
}
