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
      $model->decode_in = $data['decode_in'] ?? null;
      $model->encode_in = $data['encode_in'] ?? null;

      if (!empty($model->decode_in)) {
        $model->decode_out = rawurldecode($model->decode_in);
      }

      if (!empty($model->encode_in)) {
        $model->encode_out = rawurlencode($model->encode_in);
      }
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
