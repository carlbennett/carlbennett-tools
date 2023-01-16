<?php

namespace CarlBennett\Tools\Views;

class PasteHtml extends \CarlBennett\Tools\Views\Base\Html
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\Paste)
      throw new \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException();

    (new \CarlBennett\Tools\Libraries\Template($model, 'Paste'))->render();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
