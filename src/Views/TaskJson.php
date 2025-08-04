<?php

namespace CarlBennett\Tools\Views;

class TaskJson extends \CarlBennett\Tools\Views\Base\Json
{
  public static function invoke(\CarlBennett\Tools\Interfaces\Model $model): void
  {
    if (!$model instanceof \CarlBennett\Tools\Models\Task)
      throw new \CarlBennett\Tools\Exceptions\InvalidModelException($model);

    $model->_responseHeaders['Content-Type'] = self::mimeType();
    echo \json_encode($model->task_result, self::jsonFlags());
  }
}
