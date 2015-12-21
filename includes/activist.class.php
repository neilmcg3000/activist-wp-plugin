<?php

class Activist {
  const MANIFEST_NAME = 'cache.appcache';
  const MANIFEST_TRANSIENT = 'activist_cachemanifest';
  const SCRIPT_OPTION_KEY = 'activist_activistjs';

  public static $CACHE_MODES = array(
    "Implicit Cache" => 1,
    "All Posts" => 2
  );

  private static $initiated = false;
  private static $cache_mode = 1;

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
    add_filter('script_loader_tag', array('Activist', 'defer_script'));
    add_action('wp_head', array('Activist', 'activistcfg'));

    // allow serving the cache / frame rsrcs from index (@ wordpress rood dir)
    add_rewrite_tag('%activistrsrc%', '([a-z\.]+)');
    add_filter('query_vars', array('Activist', 'rsrc_queries'));
    add_action('template_redirect', array('Activist', 'catch_rsrcs'));

    // update the cache when urls change.
    add_action('publish_post', array('Activist', 'regen_manifest'));
    add_action('update_post', array('Activist', 'regen_manifest'));

    self::$cache_mode = apply_filters('activist_cache_mode', get_option('activist_cache_mode', 1));

    // Normal pages are only added in primary cache mode.
    if (self::$cache_mode == self::$CACHE_MODES['All Posts']) {
      add_filter('language_attributes', array('Activist', 'html_tag'));
    }
  }

  public static function html_tag($output) {
      $output .= ' manifest="' . get_bloginfo('url') . '/' . self::MANIFEST_NAME . '"';
      return $output;
  }

  public static function mime_type($rules) {
      return $rules . "AddType text/cache-manifest .appcache \n";
  }

  public static function include_script() {
    $script = Activist::toUrl(get_bloginfo('url') . '?activistrsrc=activist.js');
    wp_enqueue_script('activist', $script, array(), null, true);
  }

  // add the async attribute to the activist script.
  public static function defer_script($tag, $handle) {
    if ($handle !== 'activist') {
      return $tag;
    }
    return str_replace(' src', 'async="async" src', $tag);
  }

  public static function rsrc_queries($vars) {
    $vars[] = 'activistrsrc';
    return $vars;
  }

  public static function catch_rsrcs() {
    if (get_query_var('activistrsrc')) {
      $rsrc = get_query_var('activistrsrc');
      if ($rsrc == Activist::MANIFEST_NAME) {
        header('Content-Type: text/cache-manifest');

        // In debug mode, always regenerate the manifest.
        if (WP_DEBUG) {
          delete_transient(Activist::MANIFEST_TRANSIENT);
        }

        echo self::get_manifest();
      } else if ($rsrc == 'activist.js') {
        header('Content-Type: text/javascript');
        echo self::get_script();
      } else if ($rsrc == 'frame.html') {
        self::view('frame');
      } else if ($rsrc == 'offline.html') {
        self::view('offline');
      } else if ($rsrc == 'warning.html') {
        self::view('warning');
      }
      exit();
    }
  }

  public static function activistcfg() {
    $script = Activist::toUrl(get_bloginfo('url') . '?activistrsrc=activist.js');
    $frame = Activist::toUrl(get_bloginfo('url') . '?activistrsrc=frame.html');
    $offline = self::get_fallback_urls()["default"];
    $censor = self::get_censor_urls()["default"];
    echo("<script type='text/javascript'>");
    echo("window.activistcfg={
      url:'$script',
      frame:'$frame',
      offline:'$offline',
      message:'<meta http-equiv=refresh content=\"0; url=$censor\" />'
    };");
    echo("</script>");
  }

  public static function get_script() {
    $script = get_site_option(Activist::SCRIPT_OPTION_KEY);
    if (!$script) {
      $script = self::load_script();
    }
    return $script;
  }

  private static function load_script() {
    $data = "";
    try {
      $data = file_get_contents(ACTIVIST__PLUGIN_DIR . '/activist.js');
    } catch (Exception $e) {
      error_log("[Fatal!] Failed to read packaged resource: " + $e->toMessage());
    }
    update_site_option(Activist::SCRIPT_OPTION_KEY, $data);
    return $data;
  }

  private static function is_mediatype($file) {
    $types = array("js", "css", "png", "jpg", "jpeg", "gif", "svg");
    $ext = substr($file, strrpos($file, ".") + 1);

    return in_array($ext, $types);
  }

  public static function get_manifest() {
    $manifest = get_transient(Activist::MANIFEST_TRANSIENT);
    if (!$manifest) {
      $manifest = self::regen_manifest();
    }
    if (is_array($manifest)) {
      if (strpos($_SERVER["HTTP_USER_AGENT"], "Firefox") > 0) {
        return $manifest["firefox"];
      } else {
        return $manifest["default"];
      }
    } else {
      return $manifest;
    }
  }

  public static function regen_manifest() {
    // collect files to cache
    $files_to_cache = array();
    $files_to_cache[] = "?activistrsrc=activist.js";

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
            if(self::is_mediatype($file->getFilename())) {
                array_push($files_to_cache, str_replace(ABSPATH, '', $file));
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
    set_transient(Activist::MANIFEST_TRANSIENT, $manifest, 7 * DAY_IN_SECONDS);
    return $manifest;
  }

  // provide an absolute path URL appropriate for the cache manifest file.
  private static function toURL($file) {
    $relativeURL = parse_url(get_bloginfo('url'), PHP_URL_PATH);
    return str_replace('\\', '/', str_replace(ABSPATH, $relativeURL . '/', $file));
  }

  private static function construct_manifest_full($files) {
    $files[] = self::get_censor_urls()["default"];
    $manifest = "CACHE MANIFEST
# %s

CACHE:
%s

NETWORK:
*
";
    return sprintf($manifest, date('d-m-y H:i:s'), implode("\n", $files));
  }

  /**
   * Get the URL for the site to 'fall back on' when the server can't be reached.
   * Determined by the 'activist_offline_behavior' option set in the admin config.
   *
   * The appcache specification indicates that path-absolute (starting with /)
   * urls are rooted at the same directory as the cache manifest. (so that if
   * i control a subdirectory on the server, I can only set the caching behavio
   * for that app). Firefox follows this spec; chrome does not. This forces a
   * useragent detection to determine where the URL is rooted when printed.
   * This function provides both, and useragent determination happens in the
   * @see{get_manifest} function.
   */
  private static function get_fallback_urls()
  {
    return self::get_urls(get_option('activist_offline_behavior', 0), "offline.html");
  }

  private static function get_censor_urls()
  {
    return self::get_urls(get_option('activist_censor_behavior', 0), "warning.html");
  }

  private static function get_urls($pageid, $default) {
    if ($pageid > 0) {
      return array(
        "firefox" => str_replace(get_bloginfo('url'), '', get_permalink($pageid)),
        "default" => self::toURL(get_permalink($pageid))
      );
    } else {
      return array(
        "firefox" => "?activistrsrc=" . $default,
        "default" => "?activistrsrc=" . $default
      );
    }
  }

  private static function construct_manifest_fb($files) {
    $fbs = self::get_fallback_urls();
    $censors = self::get_censor_urls();
    $outs = [];
    foreach($fbs as $browser => $fb) {
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
      $outs[$browser] = sprintf($manifest,
        date('d-m-y H:i:s'),
        implode("\n", $files) . "\n" . $censors[$browser],
        $fb);
    }
    return $outs;
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
