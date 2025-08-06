<?php

namespace CarlBennett\Tools\Models\Plex;

class Users extends \CarlBennett\Tools\Models\User\ActiveUser implements \JsonSerializable
{
    public mixed $hl = null; // highlight
    public mixed $id = null;
    public mixed $show_hidden = null;
    public array|false|null $users = null;

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
