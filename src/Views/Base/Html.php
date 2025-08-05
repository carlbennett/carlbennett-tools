<?php /* vim: set colorcolumn=: */

namespace CarlBennett\Tools\Views\Base;

abstract class Html implements \CarlBennett\Tools\Interfaces\View
{
    public const MIMETYPE_HTML = 'text/html';

    /**
     * Provides the MIME-type that this View prints.
     *
     * @return string The MIME-type for this View class.
     */
    public static function mimeType(): string
    {
        return \sprintf('%s;charset=utf-8', self::MIMETYPE_HTML);
    }
}
