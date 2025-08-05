<?php

namespace CarlBennett\Tools\Models;

class WhoisService extends ActiveUser
{
    public bool $acl = false;
    public ?string $query = null;
    public ?array $result = null;
}
