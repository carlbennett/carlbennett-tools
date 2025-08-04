<?php

namespace CarlBennett\Tools\Views;

class PageNotFoundPlain extends \CarlBennett\Tools\Views\Base\Plain
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\PageNotFound)
      throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

    $model->_responseHeaders['Content-Type'] = self::mimeType();
    echo 'Page Not Found';
  }
}
