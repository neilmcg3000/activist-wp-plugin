<?php

class Activist {
  const MANIFEST_NAME = 'cache.manifest';

  public static $CACHE_MODES = array(
    "Implicit Cache" => 1,
    "All Posts" => 2
  );

  private static $initiated = false;
  private static $cache_mode = 1;
  private static $auto_update = true;

  public static function init() {
    if (!self::$initiated) {
      self::init_hooks();
    }
  }

  /**
   * Initializes WordPress hooks
   */
  private static function init_hooks() {
    self::$initiated = true;

    // add hooks to make browsers recognize the cache
    add_filter('mod_rewrite_rules', array('Activist', 'mime_type'));
    add_action('wp_enqueue_scripts', array('Activist', 'include_script'));

    // update the cache when urls change.
    add_action('publish_post', array('Activist', 'regen_manifest'));
    add_action('update_post', array('Activist', 'regen_manifest'));

    self::$cache_mode = apply_filters('activist_cache_mode', get_option('activist_cache_mode', 1));
    self::$auto_update = apply_filters('activist_auto_update', get_option('activist_auto_update', 1));

    // Normal pages are only added in primary cache mode.
    if (self::$cache_mode == self::$CACHE_MODES['All Posts']) {
      add_filter('language_attributes', array('Activist', 'html_tag'));
    }
    if (self::$auto_update) {
      require_once(ACTIVIST__PLUGIN_DIR . 'includes/activist-updates.class.php');
      Activist_Updates::init();
    }
  }

  public static function html_tag($output) {
      $output .= ' manifest="' . self::MANIFEST_NAME . '"';
      return $output;
  }

  public static function mime_type($rules) {
      return $rules . "AddType text/cache-manifest .manifest \n";
  }

  public static function include_script() {
    wp_enqueue_script('activist', ACTIVIST__PLUGIN_DIR . 'activist.js', array(), null);
  }

  private static function is_mediatype($file) {
    $types = array("js", "css", "png", "jpg", "jpeg", "gif");
    $type = array_pop(explode('.', $file));

    return in_array($type, types);
  }

  public static function regen_manifest() {
    // collect files to cache
    $files_to_cache = array();
    $files_to_cache[] = Activist::toUrl(ACTIVIST__PLUGIN_DIR . 'activist.js');

    // save all posts?
    //TODO: support recent content only.
    if (self::$cache_mode == self::$CACHE_MODES['All Posts']) {
      // Caching posts
      $posts = get_children(array(
        'post_status' => 'publish'
      ));

      foreach ($posts as $post_id => $post) {
        array_push($files_to_cache, get_permalink($post_id));
      }
    }

    // Theme files
    $themedir = get_template_directory();
    $dir = new RecursiveDirectoryIterator($themedir);
    foreach (new RecursiveIteratorIterator($dir) as $file) {
        if ($file->IsFile() && substr($file->getFilename(), 0, 1) != ".") {
            if(self::is_mediatype($file)) {
                array_push($files_to_cache, Activist::toURL($file));
            }
        }
    }

    // compile manifest file
    $manifest = "";
    if (self::$cache_mode == self::$CACHE_MODES['All Posts']) {
      $manifest = self::construct_manifest_full($files_to_cache);
    } else {
      $manifest = self::construct_manifest_fb($files_to_cache);
    }
    self::write_cache_manifest($manifest);
  }

  private static function toURL($file) {
    return str_replace('\\','/', str_replace(ABSPATH, get_bloginfo('url') . '/', $file));
  }

  private static function construct_manifest_full($files) {
    $manifest = "CACHE MANIFEST
# %s

CACHE:
%s

NETWORK:
*
";
    return sprintf($manifest, date('d-m-y H:i:s'), implode("\n", $files));
  }

  private static function construct_manifest_fb($files) {
    $manifest = "CACHE MANIFEST
# %s

CACHE:
%s

FALLBACK:
/ %s

# Breaks firefox
#NETWORK:
#*
";
    return sprintf($manifest, date('d-m-y H:i:s'), implode("\n", $files), Activist::toUrl(ACTIVIST__PLUGIN_DIR . 'fallback.html'));
  }

  private static function write_cache_manifest($data) {
    $fh = fopen(ABSPATH . '/' . self::MANIFEST_NAME, 'w');

    fwrite($fh, $data);
    fclose($fh);
  }

  public static function view($name, array $args = array()) {
    foreach ($args as $key => $val) {
  		$$key = $val;
  	}

  	load_plugin_textdomain('activist');

  	include(ACTIVIST__PLUGIN_DIR . 'views/'. $name . '.php');
  }
}

?>
