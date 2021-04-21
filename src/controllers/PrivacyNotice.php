<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Models\PrivacyNotice as PrivacyNoticeModel;

class PrivacyNotice extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new PrivacyNoticeModel();
    $model->active_user = Authentication::$user;
    $model->data_location = Common::$config->privacy->data_location;
    $model->email_domain = Common::$config->privacy->contact->email_domain;
    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
