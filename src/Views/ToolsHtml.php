<?php

namespace CarlBennett\Tools\Views;

class ToolsHtml extends \CarlBennett\Tools\Views\Base\Html
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\Tools)
      throw new \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException();

    (new \CarlBennett\Tools\Libraries\Template($model, 'Tools'))->render();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
