<?php
namespace CarlBennett\Tools\Libraries\Plex;

use \CarlBennett\Tools\Libraries\IDatabaseObject;
use \CarlBennett\Tools\Libraries\Plex\User as PlexUser;
use \CarlBennett\Tools\Libraries\User as BaseUser;
use \PDO;
use \PDOException;
use \StdClass;

class Request
{
  const DATE_SQL = 'Y-m-d H:i:s';

  const MAX_NOTES = 0;
  const MAX_TITLE = 0;

  const MEDIATYPE_FILM   = 0;
  const MEDIATYPE_SERIES = 1;

  const PRIORITY_LOW      = 0;
  const PRIORITY_NORMAL   = 1;
  const PRIORITY_HIGH     = 2;
  const PRIORITY_CRITICAL = 3;

  const REQUESTTYPE_NEW_CONTENT     = 0;
  const REQUESTTYPE_LOW_QUALITY     = 1;
  const REQUESTTYPE_MISSING_EPISODE = 2;
  const REQEUSTTYPE_MISSING_SEASON  = 3;

  const UUID_REGEX = '/([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}/';

  private $_id;

  protected $date_added;
  protected $date_resolved;
  protected $id;
  protected $media_type;
  protected $notes;
  protected $request_type;
  protected $title;
  protected $url;
  protected $year;

  public function __construct($value = null)
  {

  }

  public function allocate()
  {

  }

  public function commit()
  {

  }
}
