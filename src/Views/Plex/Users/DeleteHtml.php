<?php

namespace CarlBennett\Tools\Views\Plex\Users;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Template;
use \CarlBennett\Tools\Models\Plex\Users\UserForm as UserFormModel;

class DeleteHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof UserFormModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Plex/Users/Delete'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
