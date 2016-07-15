<?php
/**
 * carlbennett-tools (c) by Carl Bennett <carl@carlbennett.me>
 *
 * carlbennett-tools is licensed under a
 * Creative Commons Attribution-ShareAlike 4.0 International License.
 *
 * You should have received a copy of the license along with this
 * work. If not, see <http://creativecommons.org/licenses/by-sa/4.0/>.
 */

namespace CarlBennett\Tools;

use \CarlBennett\MVC\Libraries\Cache;
use \CarlBennett\MVC\Libraries\GlobalErrorHandler;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\Exceptions\ControllerNotFoundException;
use \CarlBennett\Tools\Libraries\Common;

function main() {

    if (!file_exists(__DIR__ . "/../vendor/autoload.php")) {
        http_response_code(500);
        exit("Server misconfigured. Please run `composer install`.");
    }
    require(__DIR__ . "/../vendor/autoload.php");

    GlobalErrorHandler::createOverrides();

    Common::$config = json_decode(
        file_get_contents(__DIR__ . "/../etc/config.json")
    );

    Common::$cache = new Cache(Common::$config->memcache->servers);

    Common::$router = new Router();

    // URL: /
    Common::$router->addRoute(
        "#^/$#",
        "CarlBennett\\Tools\\Controllers\\RedirectSoft",
        "CarlBennett\\Tools\\Views\\RedirectSoftHtml",
        "/user/login"
    );
    // URL: /user/login
    Common::$router->addRoute(
        "#^/user/login/?$#",
        "CarlBennett\\Tools\\Controllers\\User\\Login",
        "CarlBennett\\Tools\\Views\\User\\LoginHtml"
    );
    // URL: *
    Common::$router->addRoute(
        "#.*#",
        "CarlBennett\\Tools\\Controllers\\Maintenance",
        "CarlBennett\\Tools\\Views\\MaintenanceHtml"
    );

    Common::$router->route();
    Common::$router->send();

}

main();
