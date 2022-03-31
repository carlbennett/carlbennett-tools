<?php namespace CarlBennett\Tools\Libraries\Tasks;
use \CarlBennett\Tools\Libraries\Authentication;
class PruneUserSessionsTask extends Task
{
  public function run()
  {
    $success = Authentication::discard();
    $this->model->task_result = array('success' => $success);
    $this->model->_responseCode = ($success ? 200 : 500);
  }
}
