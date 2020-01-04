<?php

namespace CarlBennett\Tools\Views;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\Index as IndexModel;

class IndexHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof IndexModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Index'))->render();
    $model->_responseHeaders['Content-Type'] = $view->getMimeType();
  }
}
