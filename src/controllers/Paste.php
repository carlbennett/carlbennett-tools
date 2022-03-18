<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\PasteObject;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Libraries\User\Acl;
use \CarlBennett\Tools\Models\Paste as PasteModel;

use \DateInterval;
use \DateTime;
use \DateTimeZone;

class Paste extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new PasteModel();

    $model->_responseCode = 200;
    $model->active_user = Authentication::$user;
    $model->pastebin_admin = (
      $model->active_user &&
      $model->active_user->getAclObject()->getAcl(Acl::ACL_PASTEBIN_ADMIN)
    );

    $limit = 5;
    $bitmask = ($model->pastebin_admin ? 0 : null);
    $passworded = $model->pastebin_admin;
    $model->recent_pastes = PasteObject::getRecentPastes(
      $limit, $bitmask, $passworded
    );

    if ($router->getRequestMethod() == 'POST') {
      $this->handlePost($router, $model);
    }

    $view->render($model);
    return $model;
  }

  private function handlePost(Router &$router, PasteModel &$model) {
    $data = $router->getRequestBodyArray();

    $anonymous = self::_arg($data, 'anonymous', null);
    $expire = self::_arg($data, 'expire', null);
    $file = self::_arg($data, 'file', null);
    $mimetype = self::_arg($data, 'mimetype', null);
    $password = self::_arg($data, 'password', null);
    $text = self::_arg($data, 'text', null);
    $title = self::_arg($data, 'title', null);
    $unlisted = self::_arg($data, 'unlisted', null);

    if (!empty($text) && !empty($file)) {
      $model->_responseCode = 400;
      $model->error = array(
        'fields' => array('file', 'text'),
        'color' => 'danger',
        'message' => 'Conflict: Cannot upload both file and text in the same transaction.',
      );
      return false;
    }

    if (empty($text) && empty($file)) {
      return false;
    }

    if (empty($mimetype)) {
      if (!empty($text) && empty($file)) {
        $mimetype = 'text/plain';
      } else {
        $mimetype = 'application/octet-stream';
      }
    }

    if (empty($title)) { $title = 'Untitled'; }

    $expire_dt = (is_null($expire) ? null : ((new DateTime('now'))->add(
      new DateInterval(sprintf('PT%dS', (int) $expire))
    )));

    $options = 0;

    if ($unlisted) { $options |= PasteObject::OPTION_UNLISTED; }

    $paste = new PasteObject(null);

    if (!$anonymous && Authentication::$user) {
      $paste->setUser(Authentication::$user);
    }

    $paste->setTitle($title);
    $paste->setMimetype($mimetype);
    $paste->setOptionsBitmask(0); // TODO
    if (!empty($password)) {
      $paste->setPasswordHash(PasteObject::createPassword($password));
    }
    $paste->setDateExpires($expire_dt);

    $paste->setContent($text);

    $paste->commit();

    $model->_responseCode = 201; // Created
    $model->error = array(
      'fields' => (is_null($file) ? 'text' : 'file'),
      'color' => 'success',
      'message' => 'Uploaded successfully.',
    );

    return true;
  }

  private static function _arg(array &$haystack, string $key, $default) {
    return (isset($haystack[$key]) ? $haystack[$key] : $default);
  }
}
