<?php

namespace CarlBennett\Tools\Views\User;

class ProfileHtml extends \CarlBennett\Tools\Views\Base\Html
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\User\Profile)
      throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

    (new \CarlBennett\Tools\Libraries\Core\Template($model, 'User/Profile'))->render();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
