<?php

namespace CarlBennett\Tools\Controllers\Paste;

use \CarlBennett\Tools\Libraries\Core\HttpCode;
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
        } catch (InvalidArgumentException) { // Badly parsed id
            $this->model->paste_object = null;
        } catch (\UnexpectedValueException) { // Id not found
            $this->model->paste_object = null;
        }

        $paste =& $this->model->paste_object;

        $q = \CarlBennett\Tools\Libraries\Core\Router::query();
        $dl = (isset($q['dl']) ? $q['dl'] : null);

        if ($paste && $dl)
        {
            $dl_filename = \CarlBennett\Tools\Libraries\Core\StringProcessor::sanitizeForUrl($paste->getTitle());
            $dl_filename .= '.txt';
            $this->model->_responseHeaders['Content-Disposition'] = sprintf(
              'attachment;filename="%s"', $dl_filename
            );
            $this->model->_responseHeaders['Content-Length'] = (string) strlen($paste->getContent());
            $this->model->_responseHeaders['Content-Type'] = $paste->getMimetype();
            echo $paste->getContent();
        }

        $this->model->_responseCode = $this->model->paste_object ? HttpCode::HTTP_OK : HttpCode::HTTP_NOT_FOUND;
        return true;
    }
}
