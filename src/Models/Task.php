<?php

namespace CarlBennett\Tools\Models;

class Task extends \CarlBennett\Tools\Models\Base implements \JsonSerializable
{
    public mixed $auth_token = null;
    public bool $auth_token_valid = false;
    public string $task_name = '';
    public mixed $task_result = null;

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'auth_token' => $this->auth_token,
            'auth_token_valid' => $this->auth_token_valid,
            'task_name' => $this->task_name,
            'task_result' => $this->task_result,
        ]);
    }
}
