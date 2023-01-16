<?php

namespace CarlBennett\Tools\Tasks;

class TestTask extends Task
{
  public function run(): void
  {
    $this->model->task_result = 'Test task completed with no operations';
  }
}
