<?php
/**
 * Plugin Name: Activist.js
 * Plugin URI: https://activistjs.com/
 * Description: Activate your users against network disruptions
 * Version: 0.1.0
 * Author: Will Scott
 * Author URI: https://wills.co.tt
 * Network: false
 * License: Apache 2.0
 */
defined('ABSPATH') or die('');
include('includes/functions.php');

add_filter('language_attributes', 'activist_lang_add');
add_filter('mod_rewrite_rules', 'activist_rule_add');


add_action('publish_post', 'activist_regen_manifest');
add_action('update_post', 'activist_regen_manifest');

add_action('wp_enqueue_scripts', 'activist_include');


?>
