<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\Maintenance as MaintenanceModel;

class Maintenance extends Controller {

  public function &run(Router &$router, View &$view) {

    $model = new MaintenanceModel();

    $view->render($model);

    $model->_responseCode = 503;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
