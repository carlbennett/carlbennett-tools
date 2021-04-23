<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Models\PhpInfo as PhpInfoModel;

class PhpInfo extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new PhpInfoModel();
    $model->active_user = Authentication::$user;

    ob_start();
    phpinfo(INFO_ALL);
    $model->phpinfo = ob_get_clean();

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
