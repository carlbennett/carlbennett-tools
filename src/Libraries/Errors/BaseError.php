<?php

namespace CarlBennett\Tools\Libraries\Errors;

use \CarlBennett\Tools\Libraries\Core\Template;
use \ReflectionClass;

abstract class BaseError
{
    protected bool $exit = true;
    protected int $exit_code = 1;
    protected int $http_code = \CarlBennett\Tools\Libraries\Core\HttpCode::HTTP_INTERNAL_SERVER_ERROR;
    protected string $message = '';
    protected string $title = '';

    public function __construct(string $message, string $title = 'Internal Error')
    {
        $this->message = $message;
        $this->title = $title;
    }

    protected function _exit()
    {
        if (\function_exists('http_response_code'))
        {
            \http_response_code($this->http_code);
        }
        else if (\php_sapi_name() != 'cli')
        {
            \header(
                \getenv('SERVER_PROTOCOL') . ' ' . $this->http_code,
                true,
                $this->http_code
            );
        }

        exit((int) $this->exit_code);
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function render()
    {
        $tpl = 'Errors/' . (new ReflectionClass(get_class($this)))->getShortName();
        (new Template($this, $tpl))->render();
        if ($this->exit) $this->_exit();
    }
}
