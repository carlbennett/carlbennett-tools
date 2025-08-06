<?php

namespace CarlBennett\Tools\Models;

class Tools extends \CarlBennett\Tools\Models\User\ActiveUser implements \JsonSerializable
{
    public array $routes = [];

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), ['routes' => $this->routes]);
    }
}
