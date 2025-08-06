<?php

namespace CarlBennett\Tools\Models;

class WhoisService extends \CarlBennett\Tools\Models\User\ActiveUser implements \JsonSerializable
{
    public bool $acl = false;
    public ?string $query = null;
    public ?array $result = null;

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'acl' => $this->acl,
            'query' => $this->query,
            'result' => $this->result,
        ]);
    }
}
