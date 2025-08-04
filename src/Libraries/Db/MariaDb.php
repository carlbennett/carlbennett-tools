<?php /* vim: set colorcolumn=: */

namespace CarlBennett\Tools\Libraries\Db;

use \PDO;

class MariaDb extends PDO
{
  private static ?self $instance = null;

  public function __construct(string $driver = 'mysql', int|string $server_id = 0)
  {
    $config = \CarlBennett\Tools\Libraries\Core\Config::instance()->root[$driver];
    if (!$config) throw new \LogicException('Database driver config is invalid');

    $hostname = $config['servers'][$server_id]['hostname'] ?? null;
    $port = $config['servers'][$server_id]['port'] ?? null;

    $username = $config['username'] ?? null;
    $password = $config['password'] ?? null;
    $database_name = $config['database'] ?? null;
    $character_set = $config['character_set'] ?? null;

    $dsn = \sprintf('%s:host=%s;port=%d;dbname=%s',
      $driver, $hostname, $port, $database_name
    );

    parent::__construct($dsn, $username, $password, [
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::MYSQL_ATTR_INIT_COMMAND => \sprintf('SET NAMES \'%s\'', $character_set),
    ]);
  }

  public static function instance(bool $auto_construct = true): ?self
  {
    if (!self::$instance && $auto_construct) self::$instance = new self();
    return self::$instance;
  }
}
