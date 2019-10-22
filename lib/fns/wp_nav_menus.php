<?php

namespace NCCAgent\wpnavmenus;

function custom_id_attribute ( $atts, $item, $args ) {
  if( isset( $args->menu ) && 'top-bar' == $args->menu )
      $atts['id'] = $item->post_name;

  return $atts;
}
add_filter('nav_menu_link_attributes', __NAMESPACE__ . '\\custom_id_attribute', 10, 3);