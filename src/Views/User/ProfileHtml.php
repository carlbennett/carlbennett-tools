<?php

namespace CarlBennett\Tools\Views\User;

class ProfileHtml extends \CarlBennett\Tools\Views\Base\Html
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\User\Profile)
      throw new \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException();

    (new \CarlBennett\Tools\Libraries\Template($model, 'User/Profile'))->render();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
