<?php

namespace CarlBennett\Tools\Models;

class RemoteAddress extends Base
{
    public mixed $geoip_info = null;
    public string $ip_address = '';
    public string $user_agent = '';
}
