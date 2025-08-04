<?php

namespace CarlBennett\Tools\Views;

class RemoteAddressHtml extends \CarlBennett\Tools\Views\Base\Html
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\RemoteAddress)
      throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

    (new \CarlBennett\Tools\Libraries\Core\Template($model, 'RemoteAddress'))->render();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
