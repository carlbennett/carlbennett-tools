<?php

namespace CarlBennett\Tools\Models;

class Paste extends \CarlBennett\Tools\Models\User\ActiveUser implements \JsonSerializable
{
    public ?string $id = null;
    public ?\CarlBennett\Tools\Libraries\PasteObject $paste_object = null;
    public bool $pastebin_admin = false;
    public ?array $recent_pastes = null;

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'id' => $this->id,
            'paste_object' => $this->paste_object,
            'pastebin_admin' => $this->pastebin_admin,
            'recent_pastes' => $this->recent_pastes,
        ]);
    }
}
