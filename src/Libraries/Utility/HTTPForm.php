<?php

namespace CarlBennett\Tools\Libraries\Utility;

class HTTPForm
{
    protected array $form = [];

    public function __construct(array $form = [])
    {
        $this->form = $form;
    }

    public function delete(string $key): void
    {
        if (isset($this->form[$key])) unset($this->form[$key]);
    }

    public function getAll(): array
    {
        return $this->form;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->form[$key] ?? $default;

        if (\is_string($value) && \is_numeric($value))
        {
            return \strpos($value, '.') !== false ? (double) $value : (int) $value;
        }
        else
        {
            return $value;
        }
    }

    /**
     * alias of delete()
     */
    public function remove(string $key): void
    {
        $this->delete($key);
    }

    public function set(string $key, mixed $value): void
    {
        $this->form[$key] = $value;
    }
}
