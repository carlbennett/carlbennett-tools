<?php

namespace CarlBennett\Tools\Views;

class PageNotFoundJson extends \CarlBennett\Tools\Views\Base\Json
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\PageNotFound)
      throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

    $model->_responseHeaders['Content-Type'] = self::mimeType();
    echo \json_encode(['error' => 404], self::jsonFlags());
  }
}
