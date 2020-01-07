<?php

namespace CarlBennett\Tools\Views\User;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\User\Login as LoginModel;

class LoginHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof LoginModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'User/Login'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
