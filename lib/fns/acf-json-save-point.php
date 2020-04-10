<?php

function ncc_acf_json_save_point( $path ) {
    // update path
    $path = plugin_dir_path( __FILE__ ) . '../acf-json';

    // return
    return $path;
}
add_filter('acf/settings/save_json', 'ncc_acf_json_save_point');