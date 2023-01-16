<?php

namespace CarlBennett\Tools\Models;

class Errorable extends Base implements \JsonSerializable
{
  /**
   * Stores error state information between Controller and downstream handlers, useful for Template rendering.
   *
   * @var mixed
   */
  public mixed $error = null;

  /**
   * Implements the JSON serialization function from the JsonSerializable interface.
   */
  public function jsonSerialize() : mixed
  {
    return \array_merge(parent::jsonSerialize(), ['error' => $this->error]);
  }
}
