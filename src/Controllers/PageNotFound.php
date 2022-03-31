<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Models\PageNotFound as PageNotFoundModel;

class PageNotFound extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new PageNotFoundModel();
    $view->render($model);
    $model->_responseCode = 404;
    return $model;
  }
}
