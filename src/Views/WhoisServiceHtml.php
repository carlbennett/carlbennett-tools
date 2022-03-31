<?php

namespace CarlBennett\Tools\Views;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Template;
use \CarlBennett\Tools\Models\WhoisService as WhoisServiceModel;

class WhoisServiceHtml extends View
{
  public function getMimeType()
  {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model)
  {
    if (!$model instanceof WhoisServiceModel)
    {
      throw new IncorrectModelException();
    }
    (new Template($model, 'WhoisService'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
