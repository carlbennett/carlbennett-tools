<?php

namespace CarlBennett\Tools\Views;

class RemoteAddressPlain extends \CarlBennett\Tools\Views\Base\Plain
{
    public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
    {
        if (!$model instanceof \CarlBennett\Tools\Models\RemoteAddress)
            throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

        $model->_responseHeaders['Content-Type'] = self::mimeType();
        echo $model->ip_address;
    }
}
