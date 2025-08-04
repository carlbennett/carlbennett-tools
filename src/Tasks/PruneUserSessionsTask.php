<?php

namespace CarlBennett\Tools\Tasks;

class PruneUserSessionsTask extends Task
{
  public function run(): void
  {
    $success = \CarlBennett\Tools\Libraries\Core\Authentication::discard();
    $this->model->task_result = array('success' => $success);
    $this->model->_responseCode = ($success ? 200 : 500);
  }
}
