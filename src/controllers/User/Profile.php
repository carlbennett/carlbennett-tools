<?php /* vim: set colorcolumn=: */
namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Gravatar;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Libraries\User\Invite;
use \CarlBennett\Tools\Models\User\Profile as ProfileModel;
use \DateTime;
use \LengthException;
use \UnexpectedValueException;

class Profile extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new ProfileModel();
    $model->active_user = Authentication::$user;

    if (!$model->active_user)
    {
      $model->_responseCode = 401;
      $view->render($model);
      return $model;
    }

    $model->manage = $model->active_user->getOption(User::OPTION_ACL_MANAGE_USERS);
    self::assignProfile($model);

    $model->_responseCode = 200;
    $model->feedback = array(); // for bootstrap field/color
    $query = $router->getRequestQueryArray();

    $return = $query['return'] ?? null;
    if (!empty($return) && substr($return, 0, 1) != '/') $return = null;
    if (!empty($return)) $return = Common::relativeUrlToAbsolute($return);
    $model->return = $return;

    if ($router->getRequestMethod() == 'POST')
    {
      $this->processProfile($router, $model);
      $model->manage = $model->active_user->getOption(User::OPTION_ACL_MANAGE_USERS);
    }

    if (!empty($model->return))
    {
      $model->_responseCode = 303;
      header('Location: ' . $model->return);
    }

    $view->render($model);
    return $model;
  }

  protected static function assignProfile(ProfileModel &$model)
  {
    $user = $model->active_user;

    $model->acl_invite_users = ($user ? $user->getOption(User::OPTION_ACL_INVITE_USERS) : null);
    $model->acl_manage_users = ($user ? $user->getOption(User::OPTION_ACL_MANAGE_USERS) : null);
    $model->acl_pastebin_admin = ($user ? $user->getOption(User::OPTION_ACL_PASTEBIN_ADMIN) : null);
    $model->acl_phpinfo = ($user ? $user->getOption(User::OPTION_ACL_PHPINFO) : null);
    $model->acl_plex_requests = ($user ? $user->getOption(User::OPTION_ACL_PLEX_REQUESTS) : null);
    $model->acl_plex_users = ($user ? $user->getOption(User::OPTION_ACL_PLEX_USERS) : null);
    $model->avatar = filter_var((new Gravatar($user ? $user->getEmail() : ''))->getUrl(96, 'mp'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $model->date_added = ($user ? $user->getDateAdded() : null);
    $model->date_banned = ($user ? $user->getDateBanned() : null);
    $model->date_disabled = ($user ? $user->getDateDisabled() : null);
    $model->display_name = ($user ? $user->getName() : null);
    $model->email = ($user ? $user->getEmail() : null);
    $model->internal_notes = $user->getInternalNotes();
    $model->is_banned = $user->isBanned();
    $model->is_disabled = $user->isDisabled();
    $model->record_updated = $user->getRecordUpdated();
    $model->timezone = $user->getTimezone();
  }

  protected function processProfile(Router &$router, ProfileModel &$model)
  {
    $data = $router->getRequestBodyArray();
    $now = new DateTime('now');
    $user = $model->active_user;

    $model->display_name = $data['display_name'] ?? null;
    $model->email = $data['email'] ?? null;
    $model->error = ProfileModel::ERROR_INTERNAL;
    $model->is_disabled = $data['disabled'] ?? null;
    $model->timezone = $data['timezone'] ?? null;

    if ($model->manage)
    {
      $model->acl_invite_users = $data['acl_invite_users'] ?? null;
      $model->acl_manage_users = $data['acl_manage_users'] ?? null;
      $model->acl_pastebin_admin = $data['acl_pastebin_admin'] ?? null;
      $model->acl_phpinfo = $data['acl_phpinfo'] ?? null;
      $model->acl_plex_requests = $data['acl_plex_requests'] ?? null;
      $model->acl_plex_users = $data['acl_plex_users'] ?? null;
      $model->internal_notes = $data['internal_notes'] ?? '';
      $model->is_banned = $data['banned'] ?? null;
    }

    try // -- Set email --
    {
      $user->setEmail($model->email);
    }
    catch (UnexpectedValueException $e)
    {
      $model->error = ProfileModel::ERROR_EMAIL_INVALID;
      return;
    }
    catch (LengthException $e)
    {
      $model->error = ProfileModel::ERROR_EMAIL_LENGTH;
      return;
    }
    finally // -- Update avatar with new email --
    {
      $model->avatar = (new Gravatar($user ? $user->getEmail() : ''))->getUrl(96, 'mp');
    }

    try // -- Set name --
    {
      $user->setName($model->display_name);
    }
    catch (LengthException $e)
    {
      $model->error = ProfileModel::ERROR_DISPLAY_NAME_LENGTH;
      return;
    }

    if ($model->manage)
    {
      try // -- Set internal notes --
      {
        $user->setInternalNotes($model->internal_notes);
      }
      catch (LengthException $e)
      {
        $model->error = ProfileModel::ERROR_INTERNAL_NOTES_LENGTH;
        return;
      }
    }

    try // -- Set timezone --
    {
      $user->setTimezone($model->timezone);
    }
    catch (UnexpectedValueException $e)
    {
      $model->error = ProfileModel::ERROR_TIMEZONE_INVALID;
      return;
    }
    catch (LengthException $e)
    {
      $model->error = ProfileModel::ERROR_TIMEZONE_LENGTH;
      return;
    }

    if ($model->is_disabled && !$user->isDisabled())
    {
      $user->setDateDisabled($now);
      $user->setOption(User::OPTION_DISABLED, true);
      Authentication::expireUser($user);
    }
    else if (!$model->is_disabled && $user->isDisabled())
    {
      $user->setDateDisabled(null);
      $user->setOption(User::OPTION_DISABLED, false);
    }
    $model->date_disabled = $user->getDateDisabled();
    $model->is_disabled = $user->isDisabled();

    if ($model->manage)
    {
      if ($model->is_banned && !$user->isBanned())
      {
        $user->setDateBanned($now);
        $user->setOption(User::OPTION_BANNED, true);
        Authentication::expireUser($user);
      }
      else if (!$model->is_banned && $user->isBanned())
      {
        $user->setDateBanned(null);
        $user->setOption(User::OPTION_BANNED, false);
      }
      $model->date_banned = $user->getDateBanned();
      $model->is_banned = $user->isBanned();

      $user->setOption(User::OPTION_ACL_INVITE_USERS, ($model->acl_invite_users ? true : false));
      $user->setOption(User::OPTION_ACL_MANAGE_USERS, ($model->acl_manage_users ? true : false));
      $user->setOption(User::OPTION_ACL_PASTEBIN_ADMIN, ($model->acl_pastebin_admin ? true : false));
      $user->setOption(User::OPTION_ACL_PHPINFO, ($model->acl_phpinfo ? true : false));
      $user->setOption(User::OPTION_ACL_PLEX_REQUESTS, ($model->acl_plex_requests ? true : false));
      $user->setOption(User::OPTION_ACL_PLEX_USERS, ($model->acl_plex_users ? true : false));

      $model->acl_invite_users = $user->getOption(User::OPTION_ACL_INVITE_USERS);
      $model->acl_manage_users = $user->getOption(User::OPTION_ACL_MANAGE_USERS);
      $model->acl_pastebin_admin = $user->getOption(User::OPTION_ACL_PASTEBIN_ADMIN);
      $model->acl_phpinfo = $user->getOption(User::OPTION_ACL_PHPINFO);
      $model->acl_plex_requests = $user->getOption(User::OPTION_ACL_PLEX_REQUESTS);
      $model->acl_plex_users = $user->getOption(User::OPTION_ACL_PLEX_USERS);
    }

    $user->setRecordUpdated($now);
    $model->record_updated = $user->getRecordUpdated();

    $user->commit();
    $model->error = ProfileModel::ERROR_NONE;
  }
}
