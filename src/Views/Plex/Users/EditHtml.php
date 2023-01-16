<?php

namespace CarlBennett\Tools\Views\Plex\Users;

class EditHtml extends \CarlBennett\Tools\Views\Base\Html
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\Plex\Users\UserForm)
      throw new \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException();

    (new \CarlBennett\Tools\Libraries\Template($model, 'Plex/Users/Edit'))->render();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
