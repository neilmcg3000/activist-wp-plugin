<?php
/**
 * Network functions to make sure the version of activist.js served on the
 * blog remains the newest published at the canonical github repository,
 * to allow updates to script functionality independent of wordpress bundling.
 */

class Activist_Updates {
  const REMOTE_RELEASE = 'https://registry.npmjs.com/activist/latest';
  const CHECK_INTERVAL = 604700; // just less than 7 days in seconds.

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
    add_action('activist_scheduled_update', array('Activist_Updates', 'check_for_updates'));
    self::schedule_check();
  }

  public static function schedule_check() {
    if (function_exists('wp_next_scheduled') && function_exists('wp_schedule_event')) {
      if (!wp_next_scheduled('activist_scheduled_update'))
        wp_schedule_event(time(), 'weekly', 'activist_scheduled_update');
    }
  }

  public static function check_for_updates() {
    if ((time() - get_site_option('activist_update_time') < Activist_Updates::CHECK_INTERVAL)) {
      // no more than once/week.
      return true;
    }
    update_site_option('activist_update_time', time());

    $response = wp_remote_get(Activist_Updates::REMOTE_RELEASE);
    if (is_array($response) && !is_wp_error($response)) {
      $body = $response['body'];
      try {
        $releases = json_decode($body);
        if ($release['version'] != Activist_Updates::$last_version) {
          // New Version
          self::updateActivist($release['version'], $release['dist']['tarball']);
        }
      } catch(Exception $e) {
        error_log("Failed to check for activist update: " + $e->toMessage());
      }
    }
  }

  public static function updateActivist($version, $url) {
    $response = wp_remote_get($url);
    if (is_array($response) && !is_wp_error($response)) {
      // Extract files from archive.
      $archive = $response['body'];

      $memory_stream = fopen("php://memory", 'rb+');
      fputs($memory_stream, gzdecode($archive));

      rewind($memory_stream);

      $data = self::getFileFromTarStream($memory_stream, 'package/activist.js');
      fclose($memory_stream);

      // Save to temporary.
      try {
        $fh = fopen(ACTIVIST__PLUGIN_DIR . 'activist.js', 'w');
        fwrite($fh, $data);
        fclose($fh);
      } catch (Exception $e) {
        error_log("Failed to appy activist update to disk.");
        return;
      }

      // Save that we have the new version.
      update_site_option(Activist::SCRIPT_OPTION_KEY, $data);
      update_site_option('activist_update_version', $version);
    }
  }

  // A simple Tar format parser.
  public static function getFileFromTarStream($stream, $filename) {
    $block = fread($stream, 512);
    $end = false;
    do {
      // Ends with a 0-filled record.
      $good = strncmp($block, $filename, min(100, strlen($filename))) == 0;
      $filelen = octdec(substr($block, 124, 12));

      // round up to 512.
      $blockcnt = ceil($filelen / 512);
      if ($blockcnt == 0) {
        return "";
      }
      $data = fread($stream, 512 * $blockcnt);

      if ($good) {
        return substr($data, 0, $filelen);
      } else {
        $block = fread($stream, 512);
      }
    } while(!$end);

    return "";
  }
}

?>
