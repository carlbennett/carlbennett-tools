<?php

namespace CarlBennett\Tools\Models;

class Users extends ActiveUser
{
    public ?string $hl = null; // highlight
    public ?string $id = null;
    public bool $show_hidden = false;
    public ?array $users = null;
}
