<?php namespace CarlBennett\Tools\Libraries\Tasks;
class TestTask extends Task
{
  public function run()
  {
    $this->model->task_result = 'Test task completed with no operations';
  }
}
