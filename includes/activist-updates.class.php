<?php
/**
 * Network functions to make sure the version of activist.js served on the
 * blog remains the newest published at the canonical github repository,
 * to allow updates to script functionality independent of wordpress bundling.
 */

class Activist_Updates {
  const HOST = 'github.com';
  const CHECK_INTERVAL = 604800; // 7 days in seconds.

  private static $last_version = '';
  private static $initialized = false;

  public static function init() {
    if (!self::$initialized) {
      self::initialize();
    }
  }

  /**
   * Load state, potentially kick-off background update
   */
  public static function initialize() {
    self::$initialized = true;
  }
}

?>
