<?php

namespace CarlBennett\Tools\Models;

class RedirectSoft extends \CarlBennett\Tools\Models\Base implements \JsonSerializable
{
    public string $location = '';

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), ['location' => $this->location]);
    }
}
