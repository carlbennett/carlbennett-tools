<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\RedirectSoft as RedirectSoftModel;

class RedirectSoft extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new RedirectSoftModel();
    $model->location = array_shift($args);

    $view->render($model);

    $model->_responseCode                    = 302;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseHeaders["Location"]     = $model->location;
    $model->_responseTTL                     = 0;

    return $model;

  }

}