<?php

namespace CarlBennett\Tools\Libraries\Tasks;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DateTime;
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
            $plex_users = [];
            $users = PlexUser::getAll();

            $this->model->task_result['mapped_plex_users'] = [];
            $this->model->task_result['skipped_plex_users'] = [];
            $this->model->task_result['unmapped_plex_users'] = [];
            $this->model->task_result['unmapped_plextv_users'] = [];

            foreach (PlexTvUser::getUsers($plex_token) as $plex_user)
            {
                $plex_users[$plex_user->getId()] = $plex_user;
            }

            foreach ($users as $user)
            {
                $synced = false;

                if ($user->getOption(PlexUser::OPTION_DISABLED))
                {
                    $this->model->task_result['skipped_plex_users'][] = $user;
                    continue;
                }

                $_plex_email = $user->getPlexEmail();
                $_plex_id = $user->getPlexId();
                $_plex_title = $user->getPlexTitle();
                $_plex_username = $user->getPlexUsername();
                $_home = $user->getOption(PlexUser::OPTION_HOMEUSER);

                foreach ($plex_users as $__plex_id => $plex_user)
                {
                    $__plex_email = $plex_user->getEmail();
                    $__plex_title = $plex_user->getTitle();
                    $__plex_username = $plex_user->getUsername();
                    $__home = $plex_user->getHome();

                    if ((!is_null($_plex_id) && $__plex_id === $_plex_id)
                        || (is_null($_plex_id) && !empty($_plex_email) && strtolower($_plex_email) == strtolower($__plex_email))
                        || (is_null($_plex_id) && empty($_plex_email) && !empty($_plex_username) && strtolower($_plex_username) == strtolower($__plex_username))
                        || (is_null($_plex_id) && empty($_plex_email) && empty($_plex_username) && !empty($_plex_title) && strtolower($_plex_title) == strtolower($__plex_title)))
                    {
                        $synced = true;

                        $user->setPlexEmail($__plex_email);
                        $user->setPlexId($__plex_id);
                        $user->setPlexTitle($__plex_title);
                        $user->setPlexUsername($__plex_username);
                        $user->setOption(PlexUser::OPTION_HOMEUSER, $__home);

                        if ($_plex_email !== $__plex_email
                            || $_plex_id !== $__plex_id
                            || $_plex_title !== $__plex_title
                            || $_plex_username !== $__plex_username
                            || $_home != $__home)
                        {
                            // one of the fields has been modified
                            $user->setRecordUpdated(new DateTime('now'));
                        }

                        $user->commit();
                        $this->model->task_result['mapped_plex_users'][] = $user;
                        unset($plex_users[$__plex_id]);
                        continue 2;
                    }
                }

                if (!$synced)
                {
                    // unable to map tools db plex user to plex tv user
                    $this->model->task_result['unmapped_plex_users'][] = $user;
                }
            }

            // any leftover $plex_users that were not unset() above are dumped below
            $this->model->task_result['unmapped_plextv_users'] = $plex_users;

            // finally, report general success
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

            // if not PlexTvAPIException, re-throw for upstream handler
            if (!$e instanceof PlexTvAPIException) throw $e;
        }
        finally
        {
            $this->model->_responseCode = ($this->model->task_result['success'] ? 200 : 500);
        }
    }
}
