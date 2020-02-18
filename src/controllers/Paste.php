<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\Paste as PasteModel;

class Paste extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new PasteModel();
    $model->id = array_shift($args);
    $view->render($model);
    $model->_responseCode = 503;
    return $model;
  }
}
