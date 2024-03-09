<?php

namespace CarlBennett\Tools\Tasks;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\PlexTvAPI\Exceptions\PlexTvAPIException;
use \CarlBennett\PlexTvAPI\User as PlexTvUser;
use \CarlBennett\Tools\Libraries\Plex\User as PlexUser;
use \Throwable;

class SyncPlexUsersTask extends Task
{
    public function run(): void
    {
        $create_unmapped_users = Common::$config->tasks->plex_create_unmapped_users;
        $plex_token = Common::$config->tasks->plex_auth_token;
        $this->model->task_result = array('success' => false);

        try
        {
            $plex_users = [];
            $users = PlexUser::getAll();

            $this->model->task_result['mapped_plex_users'] = [];
            $this->model->task_result['unmapped_plex_users'] = [];
            $this->model->task_result['unmapped_plextv_users'] = [];

            foreach (PlexTvUser::getUsers($plex_token) as $plex_user)
            {
                $plex_users[$plex_user->getId()] = $plex_user;
            }

            foreach ($users as $user)
            {
                $synced = false;

                $_plex_email = $user->getPlexEmail();
                $_plex_id = $user->getPlexId();
                $_plex_thumb = $user->getPlexThumb();
                $_plex_title = $user->getPlexTitle();
                $_plex_username = $user->getPlexUsername();
                $_home = $user->getOption(PlexUser::OPTION_HOMEUSER);
                $_disabled = $user->getOption(PlexUser::OPTION_DISABLED);

                foreach ($plex_users as $__plex_id => $plex_user)
                {
                    if (self::match($user, $plex_user))
                    {
                        //  $_property is from our plex_users table
                        // $__property is from api result

                        // $users, $user is from our plex_users table
                        // $plex_users, $plex_user is from api result
                        $__plex_email = $plex_user->getEmail();
                        $__plex_thumb = $plex_user->getThumb();
                        $__plex_title = $plex_user->getTitle();
                        $__plex_username = $plex_user->getUsername();
                        $__home = $plex_user->getHome();
                        $__disabled = !self::hasLibraryAccess($plex_user);

                        $user->setPlexEmail($__plex_email);
                        $user->setPlexId($__plex_id);
                        $user->setPlexThumb($__plex_thumb);
                        $user->setPlexTitle($__plex_title);
                        $user->setPlexUsername($__plex_username);
                        $user->setOption(PlexUser::OPTION_HOMEUSER, $__home);
                        $user->setOption(PlexUser::OPTION_DISABLED, $__disabled);
                        if ($_disabled != $__disabled) $user->setDateDisabled($__disabled ? 'now' : null);

                        if ($_plex_email !== $__plex_email
                            || $_plex_id !== $__plex_id
                            || $_plex_thumb !== $__plex_thumb
                            || $_plex_title !== $__plex_title
                            || $_plex_username !== $__plex_username
                            || $_home != $__home
                            || $_disabled != $__disabled)
                        {
                            // one of the fields has been modified
                            $user->setRecordUpdated('now');
                        }

                        $what_changed = [
                            'email' => $_plex_email !== $__plex_email,
                            'id' => $_plex_id !== $__plex_id,
                            'thumb' => $_plex_thumb !== $__plex_thumb,
                            'title' => $_plex_title !== $__plex_title,
                            'username' => $_plex_username !== $__plex_username,
                            'home' => $_home != $__home,
                            'disabled' => $_disabled != $__disabled,
                        ];
                        if ([false, false, false, false, false, false, false] == \array_values($what_changed))
                        {
                            $what_changed = false;
                        }

                        $user->commit();
                        $synced = true;
                        $this->model->task_result['mapped_plex_users'][] = [
                            'plex_user' => $user,
                            'plextv_user' => $plex_user,
                            'what_changed' => $what_changed,
                        ];
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

            if ($create_unmapped_users)
            {
                $created_plex_users = [];
                foreach ($plex_users as $__plex_id => $unmapped_plextv_user)
                {
                    $__plex_email = $unmapped_plextv_user->getEmail();
                    $__plex_thumb = $unmapped_plextv_user->getThumb();
                    $__plex_title = $unmapped_plextv_user->getTitle();
                    $__plex_username = $unmapped_plextv_user->getUsername();
                    $__home = $unmapped_plextv_user->getHome();
                    $__disabled = !self::hasLibraryAccess($unmapped_plextv_user);

                    $new_plex_user = new PlexUser(null);

                    $new_plex_user->setPlexEmail($__plex_email);
                    $new_plex_user->setPlexId($__plex_id);
                    $new_plex_user->setPlexThumb($__plex_thumb);
                    $new_plex_user->setPlexTitle($__plex_title);
                    $new_plex_user->setPlexUsername($__plex_username);

                    $new_plex_user->setOption(PlexUser::OPTION_HOMEUSER, $__home);
                    $new_plex_user->setOption(PlexUser::OPTION_DISABLED, $__disabled);
                    $new_plex_user->setDateDisabled($__disabled ? 'now' : null);

                    $new_plex_user->setNotes('Created automatically by sync task.');

                    if ($new_plex_user->commit())
                    {
                        $created_plex_users[] = $new_plex_user;
                    }
                }
                $this->model->task_result['created_plex_users'] = $created_plex_users;
            }

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

    /**
     * Determines if the PlexUser object (from our DB) matches the PlevTvUser object (from plex.tv API).
     * Compares Id, Email, Username, Title, in that order, case-insensitive.
     *
     * @param PlexUser $our_user The object from our database.
     * @param PlexTvUser $api_user The object from the plex.tv API.
     * @return bool Whether the objects match (true) or do not match (false).
     */
    protected static function match(PlexUser $our_user, PlexTvUser $api_user): bool
    {
        if (!\is_null($our_user->getPlexId()) && $our_user->getPlexId() === $api_user->getId()) return true;

        if (\is_string($our_user->getPlexEmail()) && \is_string($api_user->getEmail())
            && \strtolower($our_user->getPlexEmail()) == \strtolower($api_user->getEmail())) return true;

        if (\is_string($our_user->getPlexUsername()) && \is_string($api_user->getUsername())
            && \strtolower($our_user->getPlexUsername()) == \strtolower($api_user->getUsername())) return true;

        if (\is_string($our_user->getPlexTitle()) && \is_string($api_user->getTitle())
            && \strtolower($our_user->getPlexTitle()) == \strtolower($api_user->getTitle())) return true;

        return false;
    }

    protected static function hasLibraryAccess(PlexTvUser $api_user): bool
    {
        $servers = $api_user->getServers();
        if (\count($servers) == 0) return false;

        foreach ($servers as $server)
        {
            $server_json = $server->jsonSerialize();

            $allLibraries = $server_json['allLibraries'];
            $numLibraries = $server_json['numLibraries'];

            if ($allLibraries || $numLibraries > 0) return true;
        }

        return false;
    }
}
