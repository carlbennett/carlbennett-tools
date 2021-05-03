<?php
/**
 * The application entrypoint, where main initialization occurs.
 *
 * @package CarlBennett\Tools
 * @author Carl Bennett <carl@carlbennett.me>
 * @copyright 2016-2020 Carl Bennett
 * @license Proprietary
 */
declare(strict_types=1);

namespace CarlBennett\Tools;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\GlobalErrorHandler;
use \CarlBennett\MVC\Libraries\Router;

use \CarlBennett\Tools\Libraries\Authentication;

function main() {

    if (!file_exists(__DIR__ . '/../lib/autoload.php')) {
        http_response_code(500);
        exit('Server misconfigured. Please run `composer install`.');
    }
    require(__DIR__ . '/../lib/autoload.php');

    GlobalErrorHandler::createOverrides();

    date_default_timezone_set('Etc/UTC');

    Common::$config = json_decode(file_get_contents(
        __DIR__ . '/../etc/config.json'
    ));

    DatabaseDriver::$character_set = Common::$config->mysql->character_set;
    DatabaseDriver::$database_name = Common::$config->mysql->database;
    DatabaseDriver::$password      = Common::$config->mysql->password;
    DatabaseDriver::$servers       = Common::$config->mysql->servers;
    DatabaseDriver::$timeout       = Common::$config->mysql->timeout;
    DatabaseDriver::$timezone      = Common::$config->mysql->timezone;
    DatabaseDriver::$username      = Common::$config->mysql->username;

    Authentication::verify();

    $router = new Router(
      'CarlBennett\\Tools\\Controllers\\',
      'CarlBennett\\Tools\\Views\\'
    );

    if (Common::$config->maintenance[0]) {

        // URL: *
        $router->addRoute(
            '#.*#', 'Maintenance', 'MaintenanceHtml',
            Common::$config->maintenance[1]
        );

    } else {

        // URL: /
        $router->addRoute(
            '#^/$#', 'RedirectSoft', 'RedirectSoftHtml', '/tools'
        );
        // URL: /gandalf
        $router->addRoute(
            '#^/gandalf$#', 'Gandalf', 'GandalfHtml'
        );
        // URL: /paste
        $router->addRoute(
            '#^/paste$#', 'Paste', 'PasteHtml'
        );
        // URL: /paste/:id
        $router->addRoute(
            '#^/paste/([A-Za-z0-9\-]+)$#', 'Paste\\View', 'Paste\\ViewHtml'
        );
        // URL: /phpinfo
        $router->addRoute(
            '#^/phpinfo$#', 'PhpInfo', 'PhpInfoHtml'
        );
        // URL: /plex/requests
        $router->addRoute(
            '#^/plex/requests$#', 'Plex\\Requests', 'Plex\\RequestsHtml'
        );
        // URL: /plex/users
        $router->addRoute(
            '#^/plex/users$#', 'Plex\\Users', 'Plex\\UsersHtml'
        );
        // URL: /plex/users/add
        $router->addRoute(
            '#^/plex/users/add$#',
            'Plex\\Users\\Add', 'Plex\\Users\\AddHtml'
        );
        // URL: /plex/users/edit
        $router->addRoute(
            '#^/plex/users/edit$#',
            'Plex\\Users\\Edit', 'Plex\\Users\\EditHtml'
        );
        // URL: /plex/welcome
        $router->addRoute(
            '#^/plex/welcome$#', 'Plex\\Welcome', 'Plex\\WelcomeHtml'
        );
        // URL: /privacy
        $router->addRoute(
            '#^/privacy$#', 'PrivacyNotice', 'PrivacyNoticeHtml'
        );
        // URL: /remoteaddress /remoteaddress.html
        $router->addRoute(
            '#^/remoteaddress(?:\.html?)?$#',
            'RemoteAddress', 'RemoteAddressHtml'
        );
        // URL: /remoteaddress.json
        $router->addRoute(
            '#^/remoteaddress\.json$#', 'RemoteAddress', 'RemoteAddressJSON'
        );
        // URL: /remoteaddress.txt
        $router->addRoute(
            '#^/remoteaddress\.txt$#', 'RemoteAddress', 'RemoteAddressPlain'
        );
        // URL: /tools
        $router->addRoute(
            '#^/tools$#', 'Tools', 'ToolsHtml'
        );
        // URL: /urlencodedecode
        $router->addRoute(
            '#^/urlencodedecode$#', 'UrlEncodeDecode', 'UrlEncodeDecodeHtml'
        );
        // URL: /user/invite
        $router->addRoute(
            '#^/user/invite$#', 'User\\Invite', 'User\\InviteHtml'
        );
        // URL: /user/login
        $router->addRoute(
            '#^/user/login$#', 'User\\Login', 'User\\LoginHtml'
        );
        // URL: /user/logout
        $router->addRoute(
            '#^/user/logout$#', 'User\\Logout', 'User\\LogoutHtml'
        );
        // URL: *
        $router->addRoute(
            '#.*#', 'PageNotFound', 'PageNotFoundHtml'
        );

    }

    $router->route();
    $router->send();

}

main();
