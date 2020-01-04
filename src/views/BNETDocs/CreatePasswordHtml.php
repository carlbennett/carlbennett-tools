<?php

namespace CarlBennett\Tools\Views\BNETDocs;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\BNETDocs\CreatePassword as CreatePasswordModel;

class CreatePasswordHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof CreatePasswordModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'BNETDocs/CreatePassword'))->render();
    $model->_responseHeaders['Content-Type'] = $view->getMimeType();
  }
}
