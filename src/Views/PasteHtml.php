<?php

namespace CarlBennett\Tools\Views;

class PasteHtml extends \CarlBennett\Tools\Views\Base\Html
{
    public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
    {
        if (!$model instanceof \CarlBennett\Tools\Models\Paste)
            throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

        (new \CarlBennett\Tools\Libraries\Core\Template($model, 'Paste'))->render();
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
