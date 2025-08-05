<?php

namespace CarlBennett\Tools\Controllers;

use \CarlBennett\Tools\Libraries\Core\HttpCode;

class WhoisService extends Base
{
    public function __construct()
    {
        $this->model = new \CarlBennett\Tools\Models\WhoisService();
    }

    public function invoke(?array $args): bool
    {
        $this->model->acl = $this->model->active_user && $this->model->active_user->getAclObject()->getAcl(\CarlBennett\Tools\Libraries\User\Acl::ACL_WHOIS_SERVICE);
        $this->model->result = null;

        if (!$this->model->acl)
        {
            $this->model->_responseCode = HttpCode::HTTP_UNAUTHORIZED;
            return true;
        }

        $q = new \CarlBennett\Tools\Libraries\Utility\HTTPForm(\CarlBennett\Tools\Libraries\Core\Router::query());
        $this->model->query = $q->get('q');

        if (!empty($this->model->query))
        {
            $result = [];
            try
            {
                $whois = \Iodev\Whois\Factory::get()->createWhois();

                if (1 === preg_match('/^ASN?[0-9]+$/i', $this->model->query))
                {
                    // Getting raw-text lookup
                    $result['asn.lookup'] = $whois->lookupAsn($this->model->query)->text;
                }
                else
                {
                    // Checking availability
                    $result['domain.available'] = $whois->isDomainAvailable($this->model->query);

                    // Getting raw-text lookup
                    $result['domain.lookup'] = $whois->lookupDomain($this->model->query)->text;
                }
            }
            catch (\Throwable $e)
            {
                if ($e instanceof \Iodev\Whois\Exceptions\ConnectionException)
                {
                    $result['error.connection'] = $e->getMessage();
                }
                else if ($e instanceof \Iodev\Whois\Exceptions\ServerMismatchException)
                {
                    $result['error.server_mismatch'] = $e->getMessage();
                }
                else if ($e instanceof \Iodev\Whois\Exceptions\WhoisException)
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
              $this->model->result = $result;
            }
        }

        $this->model->_responseCode = HttpCode::HTTP_OK;
        return true;
    }
}
