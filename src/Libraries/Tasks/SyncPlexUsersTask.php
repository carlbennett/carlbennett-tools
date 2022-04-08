<?php

namespace CarlBennett\Tools\Libraries\Tasks;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\PlexTvAPI\User as PlexTvUser;
use \CarlBennett\Tools\Libraries\Plex\User as PlexUser;
use \Throwable;

class SyncPlexUsersTask extends Task
{
    public function run()
    {
        $plex_token = Common::$config->tasks->plex_auth_token;
        $this->model->task_result = array('success' => false);

        try
        {
            $plex_users = PlexTvUser::getUsers($plex_token);

            // TODO

            $this->model->task_result['success'] = true;
        }
        catch (Throwable $e)
        {
            $this->model->task_result = [
                'success' => false,
                'exception' => [
                    'class' => \get_class($e),
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ],
            ];

            if ($this->model->task_result['exception']['code'] === 0)
                unset($this->model->task_result['exception']['code']);
        }
        finally
        {
            $this->model->_responseCode = ($this->model->task_result['success'] ? 200 : 500);
        }
    }
}
