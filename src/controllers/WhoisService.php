<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \CarlBennett\Tools\Libraries\Authentication;
use \CarlBennett\Tools\Libraries\User;
use \CarlBennett\Tools\Libraries\User\Acl;
use \CarlBennett\Tools\Libraries\Utility\HTTPForm;
use \CarlBennett\Tools\Models\WhoisService as WhoisServiceModel;

/* from composer package: io-developer/php-whois */
use Iodev\Whois\Factory;
use Iodev\Whois\Exceptions\ConnectionException;
use Iodev\Whois\Exceptions\ServerMismatchException;
use Iodev\Whois\Exceptions\WhoisException;

use \DateTimeInterface;
use \Throwable;

class WhoisService extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new WhoisServiceModel();
    $model->active_user = Authentication::$user;
    $model->acl = ($model->active_user && $model->active_user->getAclObject()->getAcl(Acl::ACL_WHOIS_SERVICE));
    $model->result = null;

    if (!$model->acl)
    {
      $view->render($model);
      $model->_responseCode = 401;
      return $model;
    }

    $query = $router->getRequestQueryArray();
    $query = new HTTPForm($query);
    $model->query = $query->get('q');

    if (!empty($model->query))
    {
      $result = array();
      try
      {
        $whois = Factory::get()->createWhois();

        if (1 === preg_match('/^ASN?[0-9]+$/i', $model->query))
        {
          // Getting raw-text lookup
          $result['asn.lookup'] = $whois->lookupAsn($model->query)->text;
        }
        else
        {
          // Checking availability
          $result['domain.available'] = $whois->isDomainAvailable($model->query);

          // Getting raw-text lookup
          $result['domain.lookup'] = $whois->lookupDomain($model->query)->text;
        }
      }
      catch (Throwable $e)
      {
        if ($e instanceof ConnectionException)
        {
          $result['error.connection'] = $e->getMessage();
        }
        else if ($e instanceof ServerMismatchException)
        {
          $result['error.server_mismatch'] = $e->getMessage();
        }
        else if ($e instanceof WhoisException)
        {
          $result['error.whois'] = $e->getMessage();
        }
        else
        {
          throw $e; // re-throw unknown exception
        }
      }
      finally
      {
        $model->result = $result;
      }
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
