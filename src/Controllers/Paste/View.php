<?php

namespace CarlBennett\Tools\Controllers\Paste;

use \InvalidArgumentException;

class View extends \CarlBennett\Tools\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \CarlBennett\Tools\Models\Paste();
  }

  public function invoke(?array $args): bool
  {
    if (\is_null($args) || \count($args) != 1) throw new InvalidArgumentException();

    $this->model->id = \array_shift($args);

    try {
      $this->model->paste_object = new \CarlBennett\Tools\Libraries\PasteObject($this->model->id);
    } catch (InvalidArgumentException $e) { // Badly parsed id
      $this->model->paste_object = null;
    } catch (\UnexpectedValueException $e) { // Id not found
      $this->model->paste_object = null;
    }

    $paste =& $this->model->paste_object;

    $q = \CarlBennett\Tools\Libraries\Router::query();
    $dl = (isset($q['dl']) ? $q['dl'] : null);

    if ($paste && $dl)
    {
      $dl_filename = \CarlBennett\MVC\Libraries\Common::sanitizeForUrl($paste->getTitle());
      $dl_filename .= '.txt';
      $this->model->_responseHeaders['Content-Disposition'] = sprintf(
        'attachment;filename="%s"', $dl_filename
      );
      $this->model->_responseHeaders['Content-Length'] = (string) strlen($paste->getContent());
      $this->model->_responseHeaders['Content-Type'] = $paste->getMimetype();
      echo $paste->getContent();
    }

    $this->model->_responseCode = $this->model->paste_object ? 200 : 404;
    return true;
  }
}
