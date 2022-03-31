<?php

namespace CarlBennett\Tools\Views\Plex;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Template;
use \CarlBennett\Tools\Models\Plex\Users as UsersModel;

class UsersHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof UsersModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Plex/Users'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
