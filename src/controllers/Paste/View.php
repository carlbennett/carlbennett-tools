<?php

namespace CarlBennett\Tools\Controllers\Paste;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View as BaseView;
use \CarlBennett\Tools\Libraries\PasteObject;
use \CarlBennett\Tools\Models\Paste as PasteModel;

class View extends Controller {
  public function &run(Router &$router, BaseView &$view, array &$args) {
    $model = new PasteModel();
    $model->id = array_shift($args);
    $view->render($model);
    $model->_responseCode = 503;
    return $model;
  }
}
