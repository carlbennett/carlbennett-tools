<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\UrlEncodeDecode as UrlEncodeDecodeModel;

class UrlEncodeDecode extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UrlEncodeDecodeModel();

    if ($router->getRequestMethod() == 'POST') {
      $data = $router->getRequestBodyArray();
      $model->decode = ($data['decode'] ?? null) ? true : false;
      $model->input = $data['input'] ?? null;

      if (!empty($model->input)) {
        $model->output = (
          $model->decode ?
          rawurldecode($model->input) : rawurlencode($model->input)
        );
      }
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
