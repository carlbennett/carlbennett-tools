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
use \CarlBennett\Tools\Libraries\Common;

function main($argc, $argv) {

    require(__DIR__ . "/../vendor/autoload.php");

    Common::$config = json_decode(
      file_get_contents(__DIR__ . "/../etc/config.json")
    );

    Common::$cache = new Cache($config->memcache->servers);

    return 0;

}

exit(main($argc, $argv));
