<?php

namespace CarlBennett\Tools\Libraries\Mail;

use \UnexpectedValueException;

class EmailMessage extends \CarlBennett\Tools\Libraries\Mail\Message
{
    const MIMETYPE_REGEX = '#^[-\w]+/[-\w]+$#i';

    protected array $bcc = [];
    protected array $cc = [];
    protected array $from = [];
    protected array $to = [];
    protected string $body_mimetype = 'text/plain';

    public function getBcc() : array
    {
        return $this->bcc;
    }

    public function getBodyMimeType() : string
    {
        return $this->body_mimetype;
    }

    public function getCc() : array
    {
        return $this->cc;
    }

    public function getFrom() : array
    {
        return $this->from;
    }

    public function getTo() : array
    {
        return $this->to;
    }

    public function setBcc(array $value) : void
    {
        $this->bcc = $value;
    }

    public function setBodyMimeType(string $value) : void
    {
        if (\preg_match(self::MIMETYPE_REGEX, $value) !== 1)
        {
            throw new UnexpectedValueException();
        }

        $this->body_mimetype = $value;
    }

    public function setCc(array $value) : void
    {
        $this->cc = $value;
    }

    public function setFrom(array $value) : void
    {
        $this->from = $value;
    }

    public function setTo(array $value) : void
    {
        $this->to = $value;
    }
}
