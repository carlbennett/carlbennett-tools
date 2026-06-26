<?php

namespace CarlBennett\Tools\Views\Minecraft;

class SetblockMacroHtml extends \CarlBennett\Tools\Views\Base\Html
{
    public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
    {
        if (!$model instanceof \CarlBennett\Tools\Models\Minecraft\SetblockMacro)
            throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

        (new \CarlBennett\Tools\Libraries\Core\Template($model, 'Minecraft/SetblockMacro'))->render();
        $model->_responseHeaders['Content-Type'] = self::mimeType();
    }
}
