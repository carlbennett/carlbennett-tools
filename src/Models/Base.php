<?php

namespace CarlBennett\Tools\Models;

abstract class Base implements \CarlBennett\Tools\Interfaces\Model, \JsonSerializable
{
    public int $_responseCode = \CarlBennett\Tools\Libraries\Core\HttpCode::HTTP_INTERNAL_SERVER_ERROR;
    public array $_responseHeaders = [
        'Cache-Control' => 'max-age=0,no-cache,no-store', // disables cache in the browser for all PHP pages by default.
        'X-Frame-Options' => 'DENY' // DENY tells the browser to prevent archaic frame/iframe embeds of all pages including from ourselves (see also: SAMEORIGIN).
    ];

    public function jsonSerialize(): mixed
    {
        return [];
    }
}
