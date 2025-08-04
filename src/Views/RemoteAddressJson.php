<?php

namespace CarlBennett\Tools\Views;

class RemoteAddressJson extends \CarlBennett\Tools\Views\Base\Json
{
  public const MAX_USER_AGENT = 0xFFFF; // prevents buffer overflow from user input

  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\RemoteAddress)
      throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

    $model->_responseHeaders['Content-Type'] = self::mimeType();
    echo \json_encode([
      'ip_address' => $model->ip_address,
      'geoip_info' => $model->geoip_info,
      'user_agent' => substr($model->user_agent, 0, self::MAX_USER_AGENT),
    ], self::jsonFlags());
  }
}
