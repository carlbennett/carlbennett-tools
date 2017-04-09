<?php

namespace CarlBennett\Tools\Controllers\BNETDocs;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Common;
use \CarlBennett\Tools\Models\BNETDocs\CreatePassword as CreatePasswordModel;

class CreatePassword extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new CreatePasswordModel();

    $post_args = $router->getRequestBodyArray();

    self::doThings($model, $post_args);

    $view->render($model);

    $model->_responseCode                    = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL                     = 0;

    return $model;

  }

  private static function doThings(CreatePasswordModel &$model, &$args) {

    $model->hash     = null;
    $model->password = null;
    $model->pepper   = null;
    $model->seed     = null;

    if (!isset($args['password'])) { return; }

    $model->password = $args['password'];

    if (!is_string($model->password)) { return; }

    $model->pepper = Common::$config->bnetdocs->user_auth_pepper;

    self::createPassword(
      $model->password,
      $model->pepper,
      $model->hash,
      $model->seed
    );

    $model->pepper = null; // For security purposes.

  }

  private static function createPassword($password, &$pepper, &$hash, &$salt) {
    $gmp  = gmp_init(time());
    $gmp  = gmp_mul($gmp, mt_rand());
    $gmp  = gmp_mul($gmp, gmp_random_bits(64));
    $salt = strtoupper(gmp_strval($gmp, 36));
    $hash = strtoupper(hash("sha256", $password.$salt.$pepper));
  }


}
