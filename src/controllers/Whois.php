<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\Whois as WhoisModel;

class Whois extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new WhoisModel();
    $query = $router->getRequestQueryArray();

    $model->query = (isset($query['q']) ? $query['q'] : null);
    $model->recursive = (isset($query['r']) ? $query['r'] : '1');

    if (!empty($model->query)) {
      $r = (!$model->recursive ? '-n ' : '');
      $model->query_result = shell_exec(
        'whois ' . $r . escapeshellcmd($model->query) . ' 2>&1'
      );
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
