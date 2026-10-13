<?php

namespace NCCAgent\optionspage;

/**
 * Registers our ACF options pages.
 *
 * Hooked to `acf/init` so ACF doesn't load its translations before `init`.
 */
function add_options_pages(){
  if( ! function_exists('acf_add_options_page') )
    return;

  \acf_add_options_page([
    'page_title'  => 'General',
    'menu_title'  => 'NCC Settings',
    'menu_slug'   => 'ncc-settings',
    'capability'  => 'edit_posts',
    'redirect'    => false,
    'icon_url'    => 'dashicons-admin-settings',
  ]);

  \acf_add_options_sub_page([
    'page_title'  => 'Email Settings',
    'menu_title'  => 'Email',
    'parent_slug' => 'ncc-settings',
  ]);
  \acf_add_options_sub_page([
    'page_title'  => 'Online Contracting Settings',
    'menu_title'  => 'Online Contracting',
    'parent_slug' => 'ncc-settings',
  ]);
}
add_action( 'acf/init', __NAMESPACE__ . '\\add_options_pages' );
