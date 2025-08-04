<?php

namespace CarlBennett\Tools\Libraries\Mail;

use \CarlBennett\Tools\Libraries\Core\Config;
use \PHPMailer\PHPMailer\Exception as PHPMailerException;
use \PHPMailer\PHPMailer\PHPMailer;

use \LogicException;

class EmailSender
{
  private static $instance;

  private function __construct()
  {
    throw new LogicException('EmailSender class should not be constructed');
  }

  public static function getInstance() : PHPMailer
  {
    if (!self::$instance)
    {
      try
      {
        self::$instance = new PHPMailer(true);
        self::$instance->isSMTP();

        self::$instance->Host = Config::instance()->root['smtp']['hostname'];
        self::$instance->Port = Config::instance()->root['smtp']['port'];
        self::$instance->SMTPSecure = Config::instance()->root['smtp']['tls'];
        self::$instance->SMTPKeepAlive = Config::instance()->root['smtp']['keepalive'];

        self::$instance->SMTPAuth = !empty(Config::instance()->root['smtp']['password']);
        self::$instance->Username = Config::instance()->root['smtp']['username'];
        self::$instance->Password = Config::instance()->root['smtp']['password'];
      }
      catch (PHPMailerException $e)
      {
        self::$instance->getSMTPInstance()->reset();
        throw $e;
      }
    }

    self::$instance->clearAddresses();
    self::$instance->clearAttachments();

    return self::$instance;
  }
}
