<?php /* vim: set colorcolumn=: */

namespace CarlBennett\Tools\Controllers;

abstract class Base implements \CarlBennett\Tools\Interfaces\Controller
{
  /**
   * The Model to be set by subclasses and used by a View.
   *
   * @var \CarlBennett\Tools\Interfaces\Model|null
   */
  public ?\CarlBennett\Tools\Interfaces\Model $model = null;
}
