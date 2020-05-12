<?php

namespace NCCAgent\wpnavmenus;

/**
 * Adds a dashboard link to the Top Bar nav
 *
 * @param      string  $items  The menu items
 * @param      object  $args   The arguments
 *
 * @return     string  The nav items.
 */
function add_dashboard_link( $items, $args ){
  $item_classes = 'menu-item menu-item-type-custom menu-item-object-custom my-dashboard';

  if( 'top-bar' == $args->menu ){
    if( is_user_logged_in() ){
      $items .= '<li class="' . $item_classes . '"><a class="elementor-item" href="' . site_url( 'dashboard' ) . '">My Dashboard</a></li>';
    } else {
      $items .= '<li class="' . $item_classes . '"><a class="elementor-item" href="' . site_url( 'login-register' ) . '">Log In or Register</a></li>';
    }
  }
  return $items;
}
add_filter( 'wp_nav_menu_items', __NAMESPACE__ . '\\add_dashboard_link', 10, 2 );

/**
 * Adds the menu item's `post_name` as the `id` attribute for the nav menu link.
 *
 * @param      array   $atts   The attributes
 * @param      object  $item   The menu item
 * @param      object  $args   The menu item arguments
 *
 * @return     array   Array of attributes for the menu item.
 */
function custom_id_attribute ( $atts, $item, $args ) {
  $menus = ['top-bar','mobile-mega-menu-extra-links'];
  if( isset( $args->menu ) && in_array( $args->menu, $menus ) )
      $atts['id'] = $item->post_name;

  if( array_key_exists('id', $atts ) && 'whats-new-2' == $atts['id'] )
    $atts['id'] = 'whats-new';

  return $atts;
}
add_filter('nav_menu_link_attributes', __NAMESPACE__ . '\\custom_id_attribute', 10, 3);