<?php

namespace CarlBennett\Tools\Exceptions;

/**
 * InvalidModelException extends InvalidArgumentException, a subclass of LogicException.
 * It is used when an unexpected Model is provided to a View.
 * A custom message is set based on the provided Model.
 */
class InvalidModelException extends \InvalidArgumentException
{
    private string $model_name = '';

    public function __construct(\CarlBennett\Tools\Interfaces\Model|string $model, int $errno = 0, ?\Throwable $previous = null)
    {
        $this->model_name = \is_string($model) ? $model : \get_class($model);
        parent::__construct(
            \sprintf('Invalid Model (%s)', $this->model_name),
            $errno,
            $previous
        );
    }

    public function getModelName() : string
    {
        return $this->model_name;
    }
}
