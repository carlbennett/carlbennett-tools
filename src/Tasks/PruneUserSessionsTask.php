<?php

namespace CarlBennett\Tools\Tasks;

use \CarlBennett\Tools\Libraries\Core\HttpCode;

class PruneUserSessionsTask extends Task
{
    public function run(): void
    {
        $success = \CarlBennett\Tools\Libraries\Core\Authentication::discard();
        $this->model->task_result = ['success' => $success];
        $this->model->_responseCode = ($success ? HttpCode::HTTP_OK : HttpCode::HTTP_INTERNAL_SERVER_ERROR);
    }
}
