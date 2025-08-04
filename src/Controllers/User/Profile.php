<?php /* vim: set colorcolumn=: */
namespace CarlBennett\Tools\Controllers\User;

use \CarlBennett\Tools\Libraries\Core\Authentication;
use \CarlBennett\Tools\Libraries\Core\Router;
use \CarlBennett\Tools\Libraries\User\Acl;
use \CarlBennett\Tools\Libraries\User\Gravatar;
use \CarlBennett\Tools\Libraries\User\User;
use \CarlBennett\Tools\Models\User\Profile as ProfileModel;
use \InvalidArgumentException;
use \LengthException;
use \UnexpectedValueException;

class Profile extends \CarlBennett\Tools\Controllers\Base
{
  public function __construct()
  {
    $this->model = new ProfileModel();
  }

  public function invoke(?array $args): bool
  {
    if (\is_null($args) || count($args) < 1) throw new InvalidArgumentException();

    $arg = \array_shift($args);
    $profile = ($arg == 'profile');

    if (!$this->model->active_user && $profile)
    {
      $this->model->_responseCode = 401;
      return true;
    }

    $this->model->manage = $this->model->active_user ? $this->model->active_user->getAclObject()->getAcl(Acl::ACL_USERS_MANAGE) : false;

    if ($profile)
    {
      $this->model->context_user = $this->model->active_user;
    }
    else
    {
      try
      {
        $this->model->context_user = new User($arg);
      }
      catch (\Throwable $e)
      {
        // InvalidArgumentException if invalid or unsuitable input value
        // UnexpectedValueException if user not found
        if ($e instanceof InvalidArgumentException || $e instanceof UnexpectedValueException)
        {
          $this->model->context_user = null;
          $this->model->_responseCode = $e instanceof InvalidArgumentException ? 400 : 404;
          return true;
        }
        else throw $e; // re-throw unknown exception
      }
    }

    $this->model->id = $this->model->context_user ? $this->model->context_user->getId() : null;
    $this->model->self_manage = ($this->model->context_user == $this->model->active_user);
    $this->assignProfile();

    $this->model->_responseCode = 200;
    $this->model->feedback = array(); // for bootstrap field/color
    $q = Router::query();

    $return = $q['return'] ?? null;
    if (!empty($return) && substr($return, 0, 1) != '/') $return = null;
    if (!empty($return)) $return = \CarlBennett\Tools\Libraries\Core\UrlFormatter::format($return);
    $this->model->return = $return;

    if (Router::requestMethod() == Router::METHOD_POST && ($this->model->manage || $this->model->self_manage))
    {
      $this->processProfile();
      $this->model->manage = $this->model->active_user ? $this->model->active_user->getAclObject()->getAcl(Acl::ACL_USERS_MANAGE) : false;
    }

    if (!empty($this->model->return))
    {
      $this->model->_responseCode = 303;
      $this->model->_responseHeaders['Location'] = $this->model->return;
    }

    return true;
  }

  protected function assignProfile(): void
  {
    $model = $this->model;
    $user = $model->context_user;
    $acl = $user ? $user->getAclObject() : null;

    $model->acl_invite_users = ($acl && $acl->getAcl(Acl::ACL_USERS_INVITE));
    $model->acl_manage_users = ($acl && $acl->getAcl(Acl::ACL_USERS_MANAGE));
    $model->acl_pastebin_admin = ($acl && $acl->getAcl(Acl::ACL_PASTEBIN_ADMIN));
    $model->acl_phpinfo = ($acl && $acl->getAcl(Acl::ACL_PHPINFO));
    $model->acl_plex_users = ($acl && $acl->getAcl(Acl::ACL_PLEX_USERS));
    $model->acl_whois_service = ($acl && $acl->getAcl(Acl::ACL_WHOIS_SERVICE));
    $model->avatar = filter_var((new Gravatar($user ? $user->getEmail() : ''))->getUrl(96, 'mp'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $model->biography = ($user ? $user->getBiography() : null);
    $model->date_added = ($user ? $user->getDateAdded() : null);
    $model->date_banned = ($user ? $user->getDateBanned() : null);
    $model->date_disabled = ($user ? $user->getDateDisabled() : null);
    $model->display_name = ($user ? $user->getName() : null);
    $model->email = ($user ? $user->getEmail() : null);
    $model->internal_notes = ($user ? $user->getInternalNotes() : null);
    $model->is_banned = ($user ? $user->isBanned() : null);
    $model->is_disabled = ($user ? $user->isDisabled() : null);
    $model->record_updated = ($user ? $user->getRecordUpdated() : null);
    $model->timezone = ($user ? $user->getTimezone() : null);
  }

  protected function processProfile(): void
  {
    $data = Router::query();
    $model = $this->model;
    $now = new \DateTimeImmutable('now');
    $user = $model->context_user;

    $model->biography = $data['biography'] ?? null;
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
      $model->acl_plex_users = $data['acl_plex_users'] ?? null;
      $model->acl_whois_service = $data['acl_whois_service'] ?? null;
      $model->internal_notes = $data['internal_notes'] ?? '';
      $model->is_banned = $data['banned'] ?? null;
    }

    try // -- Set biography --
    {
      $user->setBiography($model->biography);
    }
    catch (LengthException $e)
    {
      $model->error = ProfileModel::ERROR_BIOGRAPHY_LENGTH;
      return;
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

      $acl = $user->getAclObject();
      $acl->setAcl(Acl::ACL_USERS_INVITE, ($model->acl_invite_users ? true : false));
      $acl->setAcl(Acl::ACL_USERS_MANAGE, ($model->acl_manage_users ? true : false));
      $acl->setAcl(Acl::ACL_PASTEBIN_ADMIN, ($model->acl_pastebin_admin ? true : false));
      $acl->setAcl(Acl::ACL_PHPINFO, ($model->acl_phpinfo ? true : false));
      $acl->setAcl(Acl::ACL_PLEX_USERS, ($model->acl_plex_users ? true : false));
      $acl->setAcl(Acl::ACL_WHOIS_SERVICE, ($model->acl_whois_service ? true : false));
      $acl->commit();

      $model->acl_invite_users = $acl->getAcl(Acl::ACL_USERS_INVITE);
      $model->acl_manage_users = $acl->getAcl(Acl::ACL_USERS_MANAGE);
      $model->acl_pastebin_admin = $acl->getAcl(Acl::ACL_PASTEBIN_ADMIN);
      $model->acl_phpinfo = $acl->getAcl(Acl::ACL_PHPINFO);
      $model->acl_plex_users = $acl->getAcl(Acl::ACL_PLEX_USERS);
      $model->acl_whois_service = $acl->getAcl(Acl::ACL_WHOIS_SERVICE);
    }

    $user->setRecordUpdated($now);
    $model->record_updated = $user->getRecordUpdated();

    $user->commit();
    $model->error = ProfileModel::ERROR_NONE;
  }
}
