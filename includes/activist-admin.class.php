<?php

class Activist_Admin {
  private static $initialized = false;
  public static function init() {
    if (!self::$initialized) {
      self::initialize();
    }

    if (isset($_POST['action']) && $_POST['action'] == 'update-config') {
			self::update_config();
		}
  }

  /**
   * Load state, potentially kick-off background update
   */
  public static function initialize() {
    self::$initialized = true;

		add_action('admin_menu', array('Activist_Admin', 'admin_menu'));
  }

  public static function admin_menu() {
    $hook = add_options_page(__('Activist', 'activist'),__('Activist', 'activist'), 'manage_options', 'activist-config', array('Activist_Admin', 'display_page'));
    //add_action( "load-$hook", array( 'Activist_Admin', 'admin_help' ) );
  }

  public static function update_config() {
    if (function_exists('current_user_can') && !current_user_can('manage_options'))
			die(__('Bad Permissions.', 'activist'));

		if (!wp_verify_nonce($_POST['_wpnonce'], 'activist-config'))
			return false;

    if (isset($_POST['activist_auto_update']) && (int) $_POST['activist_auto_update'] == 1) {
      update_option('activist_auto_update', '1');
    } else {
      update_option('activist_auto_update', '0');
    }

    // Todo: validate valid page.
    if (isset($_POST['activist_offline_behavior'])) {
      update_option('activist_offline_behavior', (string)(int)($_POST['activist_offline_behavior']));
    } else {
      update_option('activist_offline_behavior', '0');
    }

    if (isset($_POST['activist_cache_mode']) && (int) $_POST['activist_cache_mode'] == 1) {
      update_option('activist_cache_mode', '1');
    } else if (isset($_POST['activist_cache_mode']) && (int) $_POST['activist_cache_mode'] == 2) {
      update_option('activist_cache_mode', '2');
    } else {
      // Default.
      update_option('activist_cache_mode', '2');
    }

    return true;
  }

  public static function get_page_url($page = 'activist-config') {
  	$args = array('page' => $page);

  	return add_query_arg($args, admin_url('options-general.php'));
  }

  public static function display_page() {
    require_once(ABSPATH . 'wp-admin/includes/nav-menu.php');

    $cache_mode = get_option('activist_cache_mode', 1);
    $auto_update = get_option('activist_auto_update', 1);
    $offline_behavior = get_option('activist_offline_behavior', 0);

    Activist::view('config', compact('cache_mode', 'auto_update', 'offline_behavior'));
  }
}

?>
