<?php

namespace CarlBennett\Tools\Models\Factorio;

class BlueprintFlipper extends \CarlBennett\Tools\Models\Base implements \JsonSerializable
{
    public ?string $action = null;
    public ?string $blueprint_in = null;
    public ?string $blueprint_json = null;
    public ?string $blueprint_out = null;
    public ?string $error = null;

    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'action' => $this->action,
            'blueprint_in' => $this->blueprint_in,
            'blueprint_json' => $this->blueprint_json,
            'blueprint_out' => $this->blueprint_out,
            'error' => $this->error,
        ]);
    }
}
