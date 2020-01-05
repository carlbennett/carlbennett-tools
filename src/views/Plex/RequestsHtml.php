<?php

namespace CarlBennett\Tools\Views\Plex;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Models\Plex\Requests as RequestsModel;

class RequestsHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof RequestsModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Plex/Requests'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
