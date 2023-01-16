<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\PasteObject;
use \CarlBennett\Tools\Libraries\Router;
use \CarlBennett\Tools\Libraries\User\Acl;
use \DateInterval;
use \DateTime;

class Paste extends Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\Paste();
  }

  public function invoke(?array $args): bool
  {
    $this->model->_responseCode = 200;
    $this->model->active_user = Authentication::$user;
    $this->model->pastebin_admin = $this->model->active_user && $this->model->active_user->getAclObject()->getAcl(Acl::ACL_PASTEBIN_ADMIN);

    $limit = 5;
    $bitmask = ($this->model->pastebin_admin ? 0 : null);
    $passworded = $this->model->pastebin_admin;
    $this->model->recent_pastes = PasteObject::getRecentPastes($limit, $bitmask, $passworded);

    if (Router::requestMethod() == Router::METHOD_POST) $this->handlePost();
    return true;
  }

  private function handlePost(): void
  {
    $data = Router::query();

    $anonymous = self::_arg($data, 'anonymous', null);
    $expire = self::_arg($data, 'expire', null);
    $file = self::_arg($data, 'file', null);
    $mimetype = self::_arg($data, 'mimetype', null);
    $password = self::_arg($data, 'password', null);
    $text = self::_arg($data, 'text', null);
    $title = self::_arg($data, 'title', null);
    $unlisted = self::_arg($data, 'unlisted', null);

    if (!empty($text) && !empty($file))
    {
      $this->model->_responseCode = 400;
      $this->model->error = [
        'fields' => ['file', 'text'],
        'color' => 'danger',
        'message' => 'Conflict: Cannot upload both file and text in the same transaction.',
      ];
      return;
    }

    if (empty($text) && empty($file)) return;

    if (empty($mimetype))
    {
      $mimetype = (!empty($text) && empty($file) ? 'text/plain' : 'application/octet-stream');
    }

    if (empty($title)) $title = 'Untitled';

    $expire_dt = (is_null($expire) ? null : ((new DateTime('now'))->add(
      new DateInterval(sprintf('PT%dS', (int) $expire))
    )));

    $options = 0;

    if ($unlisted) $options |= PasteObject::OPTION_UNLISTED;

    $paste = new PasteObject(null);

    if (!$anonymous && $this->model->active_user)
      $paste->setUser($this->model->active_user);

    $paste->setTitle($title);
    $paste->setMimetype($mimetype);
    $paste->setOptionsBitmask(0); // TODO
    if (!empty($password)) $paste->setPasswordHash(PasteObject::createPassword($password));
    $paste->setDateExpires($expire_dt);
    $paste->setContent($text);
    $paste->commit();

    $this->model->_responseCode = 201; // Created
    $this->model->error = [
      'fields' => \is_null($file) ? 'text' : 'file',
      'color' => 'success',
      'message' => 'Uploaded successfully.',
    ];
  }

  private static function _arg(array &$haystack, string $key, $default): mixed
  {
    return (isset($haystack[$key]) ? $haystack[$key] : $default);
  }
}
