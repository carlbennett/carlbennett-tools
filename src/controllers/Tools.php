<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Models\Tools as ToolsModel;

class Tools extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new ToolsModel();
    $model->active_user = Authentication::$user;
    $model->routes = $router->getRoutes();
    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
