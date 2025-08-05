<?php

namespace CarlBennett\Tools\Views;

class GandalfHtml extends \CarlBennett\Tools\Views\Base\Html
{
    public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
    {
        if (!$model instanceof \CarlBennett\Tools\Models\Gandalf)
            throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

        (new \CarlBennett\Tools\Libraries\Core\Template($model, 'Gandalf'))->render();
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
