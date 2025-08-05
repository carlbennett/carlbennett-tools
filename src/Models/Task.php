<?php

namespace CarlBennett\Tools\Models;

class Task extends Base
{
    public mixed $auth_token = null;
    public bool $auth_token_valid = false;
    public string $task_name = '';
    public mixed $task_result = null;
}
