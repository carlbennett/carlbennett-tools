<?php

namespace CarlBennett\Tools\Controllers\Plex;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Models\Plex\Requests as RequestsModel;

class Requests extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new RequestsModel();
    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
