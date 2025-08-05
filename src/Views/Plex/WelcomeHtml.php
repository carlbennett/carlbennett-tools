<?php

namespace CarlBennett\Tools\Views\Plex;

class WelcomeHtml extends \CarlBennett\Tools\Views\Base\Html
{
    public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
    {
        if (!$model instanceof \CarlBennett\Tools\Models\Plex\Welcome)
            throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

        (new \CarlBennett\Tools\Libraries\Core\Template($model, 'Plex/Welcome'))->render();
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
