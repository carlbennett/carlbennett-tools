<?php

namespace CarlBennett\Tools\Views\User;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Template;
use \CarlBennett\Tools\Models\User\Invite as InviteModel;

class InviteHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof InviteModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'User/Invite'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
