<?php

namespace CarlBennett\Tools\Models;

class PhpInfo extends \CarlBennett\Tools\Models\User\ActiveUser implements \JsonSerializable
{
    public string|false $phpinfo = false;

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), ['phpinfo' => $this->phpinfo]);
    }
}
