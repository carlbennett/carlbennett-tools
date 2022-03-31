<?php

namespace CarlBennett\Tools\Libraries\Utility;

use \InvalidArgumentException;

class HTTPForm {

  protected $form;

  public function __construct($form) {
    if (!is_null($form) && !is_array($form)) {
      throw new InvalidArgumentException();
    }
    if (is_null($form)) {
      $this->form = array();
    } else {
      $this->form = $form;
    }
  }

  public function delete($key) {
    if (isset($this->form[$key])) {
      unset($this->form[$key]);
    }
  }

  public function getAll() {
    return $this->form;
  }

  public function get($key, $default = null) {
    $value = (isset($this->form[$key]) ? $this->form[$key] : $default);

    if (is_string($value) && is_numeric($value)) {
      if (strpos($value, '.') !== false) {
        return (double) $value;
      } else {
        return (int) $value;
      }
    } else {
      return $value;
    }
  }

  /**
   * alias of delete()
   */
  public function remove($key) {
    return $this->delete($key);
  }

  public function set($key, $value) {
    $this->form[$key] = $value;
  }

}
