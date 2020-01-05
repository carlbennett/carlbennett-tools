<?php

namespace CarlBennett\Tools\Views;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\Whois as WhoisModel;

class WhoisHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof WhoisModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Whois'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
