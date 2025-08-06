<?php

namespace CarlBennett\Tools\Models;

class UrlEncodeDecode extends \CarlBennett\Tools\Models\Base implements \JsonSerializable
{
    public bool $decode = false;
    public ?string $input = null;
    public ?string $output = null;

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'decode' => $this->decode,
            'input' => $this->input,
            'output' => $this->output,
        ]);
    }
}
