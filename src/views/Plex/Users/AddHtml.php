<?php

namespace CarlBennett\Tools\Views\Plex\Users;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Models\Plex\Users\Add as AddModel;

class AddHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof AddModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Plex/Users/Add'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
