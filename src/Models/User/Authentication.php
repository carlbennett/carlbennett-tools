<?php

namespace CarlBennett\Tools\Models\User;

class Authentication extends \CarlBennett\Tools\Models\User\ActiveUser implements \JsonSerializable
{
    public ?string $email = null;
    public array $feedback = [];
    public ?string $password = null;
    public ?string $return = null;

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'email' => $this->email,
            'feedback' => $this->feedback,
            'password' => $this->password,
            'return' => $this->return,
        ]);
    }
}
