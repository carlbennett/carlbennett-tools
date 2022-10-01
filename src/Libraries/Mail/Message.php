<?php

namespace CarlBennett\Tools\Libraries\Mail;

abstract class Message
{
    protected string $body;
    protected string $subject;

    public function getBody() : string
    {
        return $this->body;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function setBody(string $value) : void
    {
        $this->body = $value;
    }

    public function setSubject(string $value) : void
    {
        $this->subject = $value;
    }
}
