<?php

namespace CarlBennett\Tools\Views\Paste;

class ViewHtml extends \CarlBennett\Tools\Views\Base\Html
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\Paste)
      throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

    (new \CarlBennett\Tools\Libraries\Core\Template($model, 'Paste/View'))->render();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
