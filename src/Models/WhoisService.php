<?php

namespace CarlBennett\Tools\Models;

class WhoisService extends ActiveUser
{
    public bool $acl;
    public ?string $query;
    public ?array $result;
}
