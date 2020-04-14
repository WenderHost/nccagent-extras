<?php

namespace NCCAgent\optionspage;

if( function_exists('acf_add_options_page') ) {
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
}