<?php
/**
 * The application entrypoint, where main initialization occurs.
 *
 * @package CarlBennett\Tools
 * @author Carl Bennett <carl@carlbennett.me>
 * @copyright 2016-2022 Carl Bennett
 * @license Proprietary
 */
declare(strict_types=1);

namespace CarlBennett\Tools;

use \CarlBennett\Tools\Libraries\Core\Authentication;
use \CarlBennett\Tools\Libraries\Core\Config;
use \CarlBennett\Tools\Libraries\Core\Router;

function main(int $argc, array $argv)
{
    if (!\file_exists(__DIR__ . '/../lib/autoload.php'))
    {
        if (\function_exists('http_response_code')) http_response_code(500);
        exit('Server misconfigured. Please run `composer install`.');
    }
    require(__DIR__ . '/../lib/autoload.php');

    \CarlBennett\Tools\Libraries\Core\Logger::registerAPMs();
    \CarlBennett\Tools\Libraries\Core\GlobalErrorHandler::createOverrides();
    \date_default_timezone_set('Etc/UTC');
    Authentication::verify();

    if (isset(Config::instance()->root['maintenance'][0]) && Config::instance()->root['maintenance'][0])
    {
        Router::$routes = [
            ['#.*#', 'Maintenance', ['MaintenanceHtml'], Config::instance()->root['maintenance'][1]],
        ];
    }
    else
    {
        $UUID_REGEX = \substr(\CarlBennett\Tools\Interfaces\DatabaseObject::UUID_REGEX, 1, -1); // trim first and last character from constant.
        Router::$routes = [
            ['#^/$#', 'RedirectSoft', ['RedirectSoftHtml'], '/tools'],
            ['#^/gandalf$#', 'Gandalf', ['GandalfHtml']],
            ['#^/paste$#', 'Paste', ['PasteHtml']],
            ['#^/paste/([A-Za-z0-9\-]+)$#', 'Paste\\View', ['Paste\\ViewHtml']],
            ['#^/phpinfo$#', 'PhpInfo', ['PhpInfoHtml']],
            ['#^/plex/users$#', 'Plex\\Users', ['Plex\\UsersHtml']],
            ['#^/plex/users/add$#', 'Plex\\Users\\Add', ['Plex\\Users\\AddHtml']],
            ['#^/plex/users/delete$#', 'Plex\\Users\\Delete', ['Plex\\Users\\DeleteHtml']],
            ['#^/plex/users/edit$#', 'Plex\\Users\\Edit', ['Plex\\Users\\EditHtml']],
            ['#^/plex/welcome$#', 'Plex\\Welcome', ['Plex\\WelcomeHtml']],
            ['#^/privacy$#', 'PrivacyNotice', ['PrivacyNoticeHtml']],
            ['#^/remoteaddress$#', 'RemoteAddress', ['RemoteAddressHtml', 'RemoteAddressJson', 'RemoteAddressPlain']],
            ['#^/remoteaddress\.html?$#', 'RemoteAddress', ['RemoteAddressHtml']],
            ['#^/remoteaddress\.json$#', 'RemoteAddress', ['RemoteAddressJson']],
            ['#^/remoteaddress\.txt$#', 'RemoteAddress', ['RemoteAddressPlain']],
            ['#^/task/(.*)/?$#', 'Task', ['TaskJson']],
            ['#^/tools$#', 'Tools', ['ToolsHtml']],
            ['#^/urlencodedecode$#', 'UrlEncodeDecode', ['UrlEncodeDecodeHtml']],
            ['#^/user/invite$#', 'User\\Invite', ['User\\InviteHtml']],
            ['#^/user/login$#', 'User\\Login', ['User\\LoginHtml']],
            ['#^/user/logout$#', 'User\\Logout', ['User\\LogoutHtml']],
            ['#^/user/(' . $UUID_REGEX . ')$#', 'User\\Profile', ['User\\ProfileHtml']],
            ['#^/users$#', 'Users', ['UsersHtml']],
            ['#^/whois$#', 'WhoisService', ['WhoisServiceHtml']],
        ];

        //Router::$route_not_found = ['HttpCode', ['HttpCodeHtml', 'HttpCodeJson', 'HttpCodePlain'], HttpCode::HTTP_NOT_FOUND];
        Router::$route_not_found = ['PageNotFound', ['PageNotFoundHtml', 'PageNotFoundJson', 'PageNotFoundPlain']];

        /*Router::$route_unauthorized = ['HttpCode', ['HttpCodeHtml', 'HttpCodeJson', 'HttpCodePlain'], HttpCode::HTTP_SEE_OTHER,
            \sprintf('/user/login?return=%s', \rawurlencode(\getenv('REQUEST_URI')))
        ];*/
    }

    Router::invoke();
}

main($argc ?? 0, $argv ?? []);
