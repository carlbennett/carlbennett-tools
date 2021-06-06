<?php namespace CarlBennett\Tools\Libraries\Tasks;
use \CarlBennett\Tools\Libraries\Authentication;
class PruneUserSessionsTask extends Task
{
  public function run()
  {
    $this->model->task_result = Authentication::discard();
    $this->model->_responseCode = ($this->model->task_result ? 200 : 500);
  }
}
