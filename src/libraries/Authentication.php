<?php

namespace CarlBennett\Tools\Libraries;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\Tools\Libraries\User;

use \DateTime;
use \DateTimeZone;
use \Exception;
use \InvalidArgumentException;
use \PDO;
use \RuntimeException;
use \UnexpectedValueException;

/**
 * Authentication
 * The class that handles authenticating and verifying a client.
 */
class Authentication {

  const COOKIE_NAME    = 'sid';
  const DATE_SQL       = 'Y-m-d H:i:s';
  const MAX_USER_AGENT = 255;
  const TTL            = 2592000; // 1 month

  /**
   * @var string $key
   */
  private static $key;

  /**
   * @var User $user
   */
  public static $user;

  /**
   * @var DateTimeZone $timezone
   */
  protected static $timezone;

  /**
   * __construct()
   * This class's constructor is private to prevent being instantiated.
   * All functionality of this class is meant to be used as a global state
   * rather than individual auth objects.
   */
  private function __construct() {}

  /**
   * discardKey()
   * Discards fingerprint by key id server-side so lookup cannot succeed.
   *
   * @param string $key The secret key.
   *
   * @return bool Indicates if the operation succeeded.
   */
  protected static function discardKey(string $key) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $stmt = Common::$database->prepare('
      DELETE FROM `user_sessions` WHERE `id` = UNHEX(:id) LIMIT 1;
    ');

    $stmt->bindParam(':id', $key, PDO::PARAM_STR);

    $r = $stmt->execute();
    $stmt->closeCursor();

    return $r;
  }

  /**
   * expireUser()
   * Sets expiration to now for all fingerprints by user id server-side so
   * lookup cannot succeed. This effectively ends all login sessions by user.
   *
   * @param User $user The User object to have all sessions ended.
   * @throws InvalidArgumentException when user id must be a non-empty string
   * @return bool Indicates if the operation succeeded.
   */
  public static function expireUser(User &$user) {
    $id = $user->getId();

    if (!is_string($id) || empty($id)) {
      throw new InvalidArgumentException('user id must be a non-empty string');
    }

    if (!self::$timezone) self::setTimezone('Etc/UTC');
    $now = (new DateTime('now', self::$timezone))->format(self::DATE_SQL);

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $stmt = Common::$database->prepare('
      UPDATE `user_sessions` SET `expires_datetime` = :dt
      WHERE `user_id` = UuidToBin(:id) AND `expires_datetime` > :dt;
    ');

    $stmt->bindParam(':id', $id, PDO::PARAM_STR);
    $stmt->bindParam(':dt', $now, PDO::PARAM_STR);

    $r = $stmt->execute();
    $stmt->closeCursor();

    return $r;
  }

  /**
   * getFingerprint()
   * Generates a fingerprint that we can use to identify a returning client.
   *
   * @param User $user The User object to be used for fingerprinting.
   *
   * @return array The fingerprint details.
   */
  protected static function getFingerprint(User &$user) {
    $fingerprint = array();

    $fingerprint['ip_address'] = getenv('REMOTE_ADDR');
    $fingerprint['user_id']    = (is_null($user) ? null : $user->getId());
    $fingerprint['user_agent'] = substr(
      getenv('HTTP_USER_AGENT'), 0, self::MAX_USER_AGENT
    );

    return $fingerprint;
  }

  /**
   * getPartialIP()
   * Gets the first /24 or /64 for IPv4 or IPv6 addresses respectively.
   *
   * @return string The partial IP address.
   */
  protected static function getPartialIP(string $ip) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
      return long2ip(ip2long($ip) & 0xFFFFFF00);
    } else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
      return inet_ntop(substr(inet_pton($ip), 0, 8) . str_repeat(chr(0), 8));
    } else {
      throw new InvalidArgumentException('$ip is not a valid IP address');
    }
  }

  /**
   * getUniqueKey()
   * Returns a unique string based on unique user data and other entropy.
   *
   * @param User $user The User object to be used for user data.
   *
   * @return string The unique string.
   */
  protected static function getUniqueKey(User &$user) {
    if (!$user instanceof User) {
      throw new InvalidArgumentException('$user is not instance of User');
    }
    return hash( 'sha1',
      mt_rand() . getenv('REMOTE_ADDR') .
      $user->getId() . $user->getEmail() . $user->getName() .
      $user->getPasswordHash()
    );
  }

  /**
   * login()
   * Tells the client's browser to store authentication info.
   * Also sets self::$key and self::$user to derived and given values.
   *
   * @param User &$user The User object.
   *
   * @return bool Indicates if the browser cookie was sent.
   */
  public static function login(User &$user) {
    if (!$user instanceof User) {
      throw new InvalidArgumentException('$user is not instance of User');
    }

    self::$key  = self::getUniqueKey($user);
    self::$user = $user;

    $fingerprint = self::getFingerprint($user);
    self::store(self::$key, $fingerprint);

    // 'domain' is an empty string to only allow this specific http host to
    // authenticate, excluding any subdomains. If we were to specify our
    // current http host, it would also include all subdomains.
    // See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
    return setcookie(
      self::COOKIE_NAME,  // name
      self::$key,         // value
      time() + self::TTL, // expire
      '/',                // path
      '',                 // domain
      true,               // secure
      true                // httponly
    );
  }

  /**
   * logout()
   * Tells the client's browser to discard authentication info.
   *
   * @return bool Indicates if the browser cookie was sent.
   */
  public static function logout() {
    self::discardKey(self::$key);

    self::$key  = '';
    self::$user = null;

    // 'domain' is an empty string to only allow this specific http host to
    // authenticate, excluding any subdomains. If we were to specify our
    // current http host, it would also include all subdomains.
    // See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
    return setcookie(
      self::COOKIE_NAME, // name
      '',                // value
      time(),            // expire
      '/',               // path
      '',                // domain
      true,              // secure
      true               // httponly
    );
  }

  /**
   * lookup()
   * Retrieves fingerprint based on secret key.
   *
   * @param string $key The secret key, typically from the client.
   *
   * @return array The fingerprint details, or false if not found.
   */
  protected static function lookup(string $key) {
    if (!self::$timezone) self::setTimezone('Etc/UTC');
    $now = (new DateTime('now', self::$timezone))->format(self::DATE_SQL);

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $fingerprint = false;

    $stmt = Common::$database->prepare('
      SELECT UuidFromBin(`user_id`) AS `user_id`, `ip_address`, `user_agent`
      FROM `user_sessions`
      WHERE `id` = UNHEX(:id) AND (
        `expires_datetime` = NULL OR
        :dt < `expires_datetime`
      ) LIMIT 1;
    ');

    $stmt->bindParam(':id', $key, PDO::PARAM_STR);
    $stmt->bindParam(':dt', $now, PDO::PARAM_STR);

    $r = $stmt->execute();

    if ($r) {
      $fingerprint = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $stmt->closeCursor();

    return $fingerprint;
  }

  /**
   * setTimezone()
   * Sets the timezone used for database operations.
   *
   * @param string $value The timezone, optional, defaults to 'Etc/UTC'
   * @throws InvalidArgumentException when value must be a string
   * @throws UnexpectedValueException when value must be a valid timezone
   * @return bool Indicates if the operation succeeded. Always true.
   */
  public static function setTimezone(string $value = 'Etc/UTC') {
    if (!is_string($value)) {
      throw new InvalidArgumentException('value must be a string');
    }

    try {
      $tz = new DateTimeZone($value);
      if (!$tz) throw new RuntimeException();
    } catch (Exception $e) {
      throw new UnexpectedValueException('value must be a valid timezone', $e);
    } finally {
      self::$timezone = $tz;
    }

    return true;
  }

  /**
   * store()
   * Stores authentication info server-side for lookup later.
   *
   * @param string $key         The secret key.
   * @param array  $fingerprint The fingerprint details.
   *
   * @return bool Indicates if the operation succeeded.
   */
  protected static function store(string $key, array &$fingerprint) {
    if (!self::$timezone) self::setTimezone('Etc/UTC');

    $user_id     = $fingerprint['user_id'];
    $ip_address  = $fingerprint['ip_address'];
    $user_agent  = $fingerprint['user_agent'];
    $created_dt  = new DateTime('now', self::$timezone);
    $created_str = $created_dt->format(self::DATE_SQL);
    $expires_dt = new DateTime(
      '@' . ($created_dt->getTimestamp() + self::TTL), self::$timezone
    );
    $expires_str = $expires_dt->format(self::DATE_SQL);

    $r = false;

    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $stmt = Common::$database->prepare('
      INSERT INTO `user_sessions` (
        `id`, `user_id`, `ip_address`, `user_agent`,
        `created_datetime`, `expires_datetime`
      ) VALUES (
        UNHEX(:id), UuidToBin(:user_id), :ip_address, :user_agent,
        :created_dt, :expires_dt
      ) ON DUPLICATE KEY UPDATE
        `ip_address` = :ip_address, `user_agent` = :user_agent
      ;
    ');

    $stmt->bindParam(':id', $key, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
    $stmt->bindParam(':user_agent', $user_agent, PDO::PARAM_STR);
    $stmt->bindParam(':created_dt', $created_str, PDO::PARAM_STR);
    $stmt->bindParam(':expires_dt', $expires_str, PDO::PARAM_STR);

    $r = $stmt->execute();
    $stmt->closeCursor();

    return $r;
  }

  /**
   * verify()
   * Restores user information if verification of identification succeeds.
   *
   * @return bool Indicates if verification succeeded.
   */
  public static function verify() {
    // get client's lookup key
    self::$key = (
      isset($_COOKIE[self::COOKIE_NAME]) ? $_COOKIE[self::COOKIE_NAME] : ''
    );

    // no user yet
    self::$user = null;

    // return if cookie is empty or not set
    if (empty(self::$key)) { return false; }

    // lookup key in our store
    $lookup = self::lookup(self::$key);

    // logout and return if we could not verify their info
    if (!$lookup) {
      self::logout();
      return false;
    }

    // logout and return if their fingerprint ip address does not match
    if (self::getPartialIP($lookup['ip_address'])
      !== self::getPartialIP(getenv('REMOTE_ADDR'))) {
      self::logout();
      return false;
    }

    // logout and return if their fingerprint user agent does not match
    if ($lookup['user_agent'] !== getenv('HTTP_USER_AGENT')) {
      self::logout();
      return false;
    }

    // verified info, let's get the user object
    if (isset($lookup['user_id'])) {
      self::$user = new User($lookup['user_id']);
    }

    // if IP is different, update session
    if ($lookup['ip_address'] !== getenv('REMOTE_ADDR')) {
      $new_fingerprint = self::getFingerprint(self::$user);
      self::store(self::$key, $new_fingerprint);
    }

    return true;
  }

}
