<?php

namespace CarlBennett\Tools\Views\User;

class LoginHtml extends \CarlBennett\Tools\Views\Base\Html
{
    public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
    {
        if (!$model instanceof \CarlBennett\Tools\Models\User\Authentication)
            throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

        (new \CarlBennett\Tools\Libraries\Core\Template($model, 'User/Login'))->render();
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
