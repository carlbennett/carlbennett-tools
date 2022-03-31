<?php

namespace CarlBennett\Tools\Views\Plex;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Template;
use \CarlBennett\Tools\Models\Plex\Welcome as WelcomeModel;

class WelcomeHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof WelcomeModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Plex/Welcome'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
