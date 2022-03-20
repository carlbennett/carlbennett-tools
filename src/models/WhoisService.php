<?php

namespace CarlBennett\Tools\Models;

use \CarlBennett\Tools\Models\ActiveUser as ActiveUserModel;
use Iodev\Whois\Whois; /* from composer package: io-developer/php-whois */

class WhoisService extends ActiveUserModel
{
    public bool $acl;
    public ?string $query;
    public ?array $result;
}
