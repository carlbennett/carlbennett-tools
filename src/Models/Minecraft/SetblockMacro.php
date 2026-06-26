<?php

namespace CarlBennett\Tools\Models\Minecraft;

class SetblockMacro extends \CarlBennett\Tools\Models\Base implements \JsonSerializable
{
    public bool $say_done = true;
    public bool $slash = false;
    public ?int $x1 = null;
    public ?int $x2 = null;
    public ?int $y1 = null;
    public ?int $y2 = null;
    public ?int $z1 = null;
    public ?int $z2 = null;
    public ?string $tile = null;
    public ?string $extra = null;
    public ?string $code = null;

    public function allFieldsSet(): bool
    {
        return !\is_null($this->x1)
            && !\is_null($this->x2)
            && !\is_null($this->y1)
            && !\is_null($this->y2)
            && !\is_null($this->z1)
            && !\is_null($this->z2)
            && !\is_null($this->tile);
    }

    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'say_done' => $this->say_done,
            'slash' => $this->slash,
            'x1' => $this->x1,
            'x2' => $this->x2,
            'y1' => $this->y1,
            'y2' => $this->y2,
            'z1' => $this->z1,
            'z2' => $this->z2,
            'tile' => $this->tile,
            'extra' => $this->extra,
            'code' => $this->code,
        ]);
    }
}
