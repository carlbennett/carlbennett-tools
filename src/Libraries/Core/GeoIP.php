<?php

namespace CarlBennett\Tools\Libraries\Core;

use \CarlBennett\Tools\Libraries\Core\Config;
use \GeoIp2\Database\Reader;

class GeoIP
{
    private static ?Reader $reader = null;

    private function __construct() {}

    protected static function getReader(): Reader
    {
        if (self::$reader) return self::$reader;

        try
        {
            self::$reader = new Reader(Config::instance()->root['geoip']['database_file']);
        }
        catch (\MaxMind\Db\Reader\InvalidDatabaseException)
        {
            // database is invalid or corrupt
            self::$reader = null;
        }

        return self::$reader;
    }

    public static function getRecord(string $address): mixed
    {
        if (!filter_var($address, FILTER_VALIDATE_IP))
        {
            throw new \UnexpectedValueException('not a valid IP address');
        }

        $mmdb = self::getReader();
        $type = Config::instance()->root['geoip']['database_type'];

        try
        {
            $record = $mmdb->$type($address);
        }
        catch (\GeoIp2\Exception\AddressNotFoundException $e)
        {
            $record = null;
        }

        return $record;
    }
}
