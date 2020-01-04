<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\Index as IndexModel;

class Index extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new IndexModel();
    $model->routes = $router->getRoutes();
    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
