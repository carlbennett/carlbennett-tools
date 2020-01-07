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

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\ControllerNotFoundException;
use \CarlBennett\MVC\Libraries\GlobalErrorHandler;
use \CarlBennett\MVC\Libraries\Router;

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

    Common::$router = new Router();

    if (Common::$config->maintenance[0]) {

        // URL: *
        Common::$router->addRoute(
            '#.*#',
            'CarlBennett\\Tools\\Controllers\\Maintenance',
            'CarlBennett\\Tools\\Views\\MaintenanceHtml',
            Common::$config->maintenance[1]
        );

    } else {

        // URL: /
        Common::$router->addRoute(
            '#^/$#',
            'CarlBennett\\Tools\\Controllers\\RedirectSoft',
            'CarlBennett\\Tools\\Views\\RedirectSoftHtml',
            '/home'
        );
        // URL: /gandalf
        Common::$router->addRoute(
            '#^/gandalf/?$#',
            'CarlBennett\\Tools\\Controllers\\Gandalf',
            'CarlBennett\\Tools\\Views\\GandalfHtml'
        );
        // URL: /home
        Common::$router->addRoute(
            '#^/home/?$#',
            'CarlBennett\\Tools\\Controllers\\Index',
            'CarlBennett\\Tools\\Views\\IndexHtml'
        );
        // URL: /plex/requests
        Common::$router->addRoute(
            '#^/plex/requests/?$#',
            'CarlBennett\\Tools\\Controllers\\Plex\\Requests',
            'CarlBennett\\Tools\\Views\\Plex\\RequestsHtml'
        );
        // URL: /plex/users
        Common::$router->addRoute(
            '#^/plex/users/?$#',
            'CarlBennett\\Tools\\Controllers\\Plex\\Users',
            'CarlBennett\\Tools\\Views\\Plex\\UsersHtml'
        );
        // URL: /whois
        Common::$router->addRoute(
            '#^/whois/?$#',
            'CarlBennett\\Tools\\Controllers\\Whois',
            'CarlBennett\\Tools\\Views\\WhoisHtml'
        );
        // URL: *
        Common::$router->addRoute(
            '#.*#',
            'CarlBennett\\Tools\\Controllers\\PageNotFound',
            'CarlBennett\\Tools\\Views\\PageNotFoundHtml'
        );

    }

    Common::$router->route();
    Common::$router->send();

}

main();
