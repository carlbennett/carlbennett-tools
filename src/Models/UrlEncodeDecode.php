<?php

namespace CarlBennett\Tools\Models;

class UrlEncodeDecode extends Base
{
    public bool $decode = false;
    public ?string $input = null;
    public ?string $output = null;
}
