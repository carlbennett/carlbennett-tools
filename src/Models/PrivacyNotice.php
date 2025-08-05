<?php

namespace CarlBennett\Tools\Models;

class PrivacyNotice extends ActiveUser
{
    public string $data_location;
    public string $email_domain;
    public string $org;
    public string $web_domain;
}
