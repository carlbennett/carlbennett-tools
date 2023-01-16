<?php

namespace CarlBennett\Tools\Views\User;

class InviteHtml extends \CarlBennett\Tools\Views\Base\Html
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\User\Invite)
      throw new \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException();

    (new \CarlBennett\Tools\Libraries\Template($model, 'User/Invite'))->render();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
