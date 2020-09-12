<?php

namespace CarlBennett\Tools\Controllers\Paste;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View as BaseView;
use \CarlBennett\Tools\Libraries\PasteObject;
use \CarlBennett\Tools\Models\Paste as PasteModel;

class View extends Controller {
  public function &run(Router &$router, BaseView &$view, array &$args) {
    $model = new PasteModel();
    $model->id = array_shift($args);

    try {
      $model->paste_object = new PasteObject($model->id);
    } catch (UnexpectedValueException $e) {
      $model->paste_object = null;
    }

    $paste =& $model->paste_object;

    $query = $router->getRequestQueryArray();
    $dl ??= $query['dl'];

    if ($paste && $dl) {
      $dl_filename = Common::sanitizeForUrl($paste->getTitle());
      $dl_filename .= '.txt';
      $model->_responseHeaders['Content-Disposition'] = sprintf(
        'attachment;filename="%s"', $dl_filename
      );
      $model->_responseHeaders['Content-Length'] = (string) strlen($paste->getContent());
      $model->_responseHeaders['Content-Type'] = $paste->getMimetype();
      echo $paste->getContent();
    } else {
      $view->render($model);
    }

    $model->_responseCode = (
      $model->paste_object instanceof PasteObject ? 200 : 404
    );
    return $model;
  }
}
