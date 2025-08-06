<?php

namespace CarlBennett\Tools\Models;

class RemoteAddress extends \CarlBennett\Tools\Models\Base implements \JsonSerializable
{
    public mixed $geoip_info = null;
    public string $ip_address = '';
    public string $user_agent = '';

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'geoip_info' => $this->geoip_info,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
        ]);
    }
}
