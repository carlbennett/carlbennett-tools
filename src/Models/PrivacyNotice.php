<?php

namespace CarlBennett\Tools\Models;

class PrivacyNotice extends \CarlBennett\Tools\Models\User\ActiveUser implements \JsonSerializable
{
    public string $data_location = '';
    public string $email_domain = '';
    public string $org = '';
    public string $web_domain = '';

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'data_location' => $this->data_location,
            'email_domain' => $this->email_domain,
            'org' => $this->org,
            'web_domain' => $this->web_domain,
        ]);
    }
}
