<?php

namespace CarlBennett\Tools\Views;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Template;
use \CarlBennett\Tools\Models\Gandalf as GandalfModel;

class GandalfHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof GandalfModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Gandalf'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
