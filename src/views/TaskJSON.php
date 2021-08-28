<?php

namespace CarlBennett\Tools\Views;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Models\Task as TaskModel;

class TaskJSON extends View
{
  public function getMimeType()
  {
    return 'application/json;charset=utf-8';
  }

  public function render(Model &$model)
  {
    if (!$model instanceof TaskModel)
    {
      throw new IncorrectModelException();
    }
    echo json_encode($model->task_result, Common::prettyJSONIfBrowser());
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
