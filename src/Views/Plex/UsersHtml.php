<?php

namespace CarlBennett\Tools\Views\Plex;

class UsersHtml extends \CarlBennett\Tools\Views\Base\Html
{
    public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
    {
        if (!$model instanceof \CarlBennett\Tools\Models\Plex\Users)
            throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

        (new \CarlBennett\Tools\Libraries\Core\Template($model, 'Plex/Users'))->render();
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
