<?php

namespace CarlBennett\Tools\Models;

class Maintenance extends \CarlBennett\Tools\Models\Base implements \JsonSerializable
{
    public string $message = '';

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), ['message' => $this->message]);
    }
}
