<?php

namespace CarlBennett\Tools\Views\User;

class LogoutHtml extends \CarlBennett\Tools\Views\Base\Html
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\User\Authentication)
      throw new \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException();

    (new \CarlBennett\Tools\Libraries\Template($model, 'User/Logout'))->render();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
