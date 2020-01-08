<?php

namespace CarlBennett\Tools\Libraries\Utility;

class DomainValidator {

  // <https://stackoverflow.com/a/4694816>
  protected static function is_valid_domain_name($domain_name)
  {
    return (
      preg_match( // valid chars check
        "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name
      )
      && preg_match( // overall length check
        "/^.{1,253}$/", $domain_name
      )
      && preg_match( // length of each label
        "/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name
      )
    );
  }

  public static function validate(string $value) {
    return self::is_valid_domain_name($value);
  }

}
