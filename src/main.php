<?php
/**
 * The application entrypoint, where main initialization occurs.
 *
 * @package CarlBennett\Tools
 * @author Carl Bennett <carl@carlbennett.me>
 * @copyright 2016-2020 Carl Bennett
 * @license Proprietary
 */

namespace CarlBennett\Tools;

use \CarlBennett\MVC\Libraries\Cache;
use \CarlBennett\MVC\Libraries\GlobalErrorHandler;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\Exceptions\ControllerNotFoundException;
use \CarlBennett\Tools\Libraries\Common;

function main() {

    if (!file_exists(__DIR__ . '/../lib/autoload.php')) {
        http_response_code(500);
        exit('Server misconfigured. Please run `composer install`.');
    }
    require(__DIR__ . '/../lib/autoload.php');

    GlobalErrorHandler::createOverrides();

    Common::$config = json_decode(
        file_get_contents(__DIR__ . '/../etc/config.json')
    );

    Common::$cache = new Cache(Common::$config->memcache->servers);

    Common::$router = new Router();

    // URL: /
    Common::$router->addRoute(
        '#^/$#',
        'CarlBennett\\Tools\\Controllers\\Index',
        'CarlBennett\\Tools\\Views\\IndexHtml'
    );
    // URL: /bnetdocs/createpassword
    Common::$router->addRoute(
        '#^/bnetdocs/createpassword/?$#',
        'CarlBennett\\Tools\\Controllers\\BNETDocs\\CreatePassword',
        'CarlBennett\\Tools\\Views\\BNETDocs\\CreatePasswordHtml'
    );
    // URL: /gandalf
    Common::$router->addRoute(
        '#^/gandalf/?$#',
        'CarlBennett\\Tools\\Controllers\\Gandalf',
        'CarlBennett\\Tools\\Views\\GandalfHtml'
    );
    // URL: *
    Common::$router->addRoute(
        '#.*#',
        'CarlBennett\\Tools\\Controllers\\Maintenance',
        'CarlBennett\\Tools\\Views\\MaintenanceHtml'
    );

    Common::$router->route();
    Common::$router->send();

}

main();
