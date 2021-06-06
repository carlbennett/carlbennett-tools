<?php namespace CarlBennett\Tools\Libraries\Tasks;
use \CarlBennett\Tools\Libraries\Authentication;
class PruneUserSessionsTask extends Task
{
  public function run()
  {
    $this->model->task_result = Authentication::discard();
  }
}
