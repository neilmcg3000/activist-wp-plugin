<?php
/**
 * Plugin Name: Activist.js
 * Plugin URI: https://activistjs.com/
 * Description: Activate your users against network disruptions
 * Version: 0.1.1
 * Author: Will Scott
 * Author URI: https://wills.co.tt
 * Network: false
 * License: BSD
 */

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
 	exit;
}

define('ACTIVIST__PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once(ACTIVIST__PLUGIN_DIR . 'includes/activist.class.php');

add_action('init', array('Activist', 'init'));

if (is_admin()) {
  require_once(ACTIVIST__PLUGIN_DIR . 'includes/activist-admin.class.php');
 	add_action('init', array('Activist_Admin', 'init'));
}

?>
