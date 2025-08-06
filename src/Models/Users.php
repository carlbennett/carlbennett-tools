<?php

namespace CarlBennett\Tools\Models;

class Users extends \CarlBennett\Tools\Models\User\ActiveUser implements \JsonSerializable
{
    public ?string $hl = null; // highlight
    public ?string $id = null;
    public bool $show_hidden = false;
    public ?array $users = null;

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'hl' => $this->hl,
            'id' => $this->id,
            'show_hidden' => $this->show_hidden,
            'users' => $this->users,
        ]);
    }
}
