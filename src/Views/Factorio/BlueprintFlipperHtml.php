<?php

namespace CarlBennett\Tools\Views\Factorio;

class BlueprintFlipperHtml extends \CarlBennett\Tools\Views\Base\Html
{
    public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
    {
        if (!$model instanceof \CarlBennett\Tools\Models\Factorio\BlueprintFlipper)
            throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

        (new \CarlBennett\Tools\Libraries\Core\Template($model, 'Factorio/BlueprintFlipper'))->render();
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
