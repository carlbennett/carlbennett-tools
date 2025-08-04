<?php

namespace CarlBennett\Tools\Views;

class PhpInfoHtml extends \CarlBennett\Tools\Views\Base\Html
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\PhpInfo)
      throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

    (new \CarlBennett\Tools\Libraries\Core\Template($model, 'PhpInfo'))->render();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
