<?php

namespace CarlBennett\Tools\Libraries\Mail;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\Tools\Libraries\Authentication;

use \PHPMailer\PHPMailer\Exception as PHPMailerException;
use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\SMTP;

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

        self::$instance->Host = Common::$config->smtp->hostname;
        self::$instance->Port = Common::$config->smtp->port;
        self::$instance->SMTPSecure = Common::$config->smtp->tls;
        self::$instance->SMTPKeepAlive = Common::$config->smtp->keepalive;

        self::$instance->SMTPAuth = !empty(Common::$config->smtp->password);
        self::$instance->Username = Common::$config->smtp->username;
        self::$instance->Password = Common::$config->smtp->password;
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
