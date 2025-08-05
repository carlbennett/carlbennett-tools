<?php

namespace CarlBennett\Tools\Models;

class Paste extends ActiveUser
{
    public ?string $id = null;
    public ?\CarlBennett\Tools\Libraries\PasteObject $paste_object = null;
    public bool $pastebin_admin = false;
    public ?array $recent_pastes = null;
}
