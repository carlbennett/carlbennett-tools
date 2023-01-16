<?php

namespace CarlBennett\Tools\Views\Plex;

class WelcomeHtml extends \CarlBennett\Tools\Views\Base\Html
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\Plex\Welcome)
      throw new \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException();

    (new \CarlBennett\Tools\Libraries\Template($model, 'Plex/Welcome'))->render();
    $model->_responseHeaders['Content-Type'] = self::mimeType();
  }
}
