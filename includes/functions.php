<?php
defined('ABSPATH') or die('');

include 'manifest.php';

define('ACTIVIST_MANIFEST_NAME', 'cache.manifest');

function activist_lang_add($output) {
    $output .= ' manifest="' . ACTIVIST_MANIFEST_NAME . '"';
    return $output;
}

function activist_rule_add($rules) {
    return $rules . "AddType text/cache-manifest .manifest \n";
}

function activist_validtypes($file) {
    $types = array("js", "css", "png", "jpg", "jpeg", "gif", "php");
    $type = array_pop(explode('.', $file));

    return in_array($type, types);
}

function activist_generate_manifest() {
    global $manifest;
    // open manifest file
    $fh = fopen(ABSPATH . '/' . ACTIVIST_MANIFEST_NAME, 'w');

    // collect files to cache
    $files_to_cache = array();
    $files_to_cache[] = plugin_dir_url(__FILE__) . 'activist.js';

    // save all posts?
    // Caching posts
    $posts = get_children(array(
        'post_type'         =>    'post',
        'post_status'       =>    'publish'
    ));

    foreach ($posts as $post_id => $post) {
        array_push($files_to_cache, get_bloginfo('url') . '/?p=' . $post_id);
    }

    // Theme files
    foreach (new RecursiveIteratorIterator($dir) as $file) {
        if ($file->IsFile() && substr($file-> getFilename(), 0, 1) != ".") {
            if(!preg_match('/.php$/', $file) && activist_validtype($file))
                array_push($files_to_cache, str_replace('\\','/',str_replace(ABSPATH, get_bloginfo('url') . '/', $file)));
            }
        }
    }

    // compile manifest file
    $manifest = sprintf($manifest, date('d-m-y H:i:s'), implode("\n", $files_to_cache));

    fwrite($fh, $manifest);
    fclose($fh);
}



function activist_post_published($post) {
    activist_generate_manifest();
}

function activist_add_script() {
    wp_enqueue_script('activist', plugin_dir_url(__FILE__) . 'activist.js', array(), null);
}

?>
