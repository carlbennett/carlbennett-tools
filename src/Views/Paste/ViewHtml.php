<?php

namespace CarlBennett\Tools\Views\Paste;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Template;
use \CarlBennett\Tools\Models\Paste as PasteModel;

class ViewHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof PasteModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Paste/View'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
