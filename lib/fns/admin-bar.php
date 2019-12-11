<?php

namespace NCCAgent\wpadmin;

/**
 * Removes the admin bar for non-administrator users.
 */
function remove_admin_bar() {
  if( ! current_user_can('administrator') && ! is_admin() ) {
    show_admin_bar(false);
  }
}
add_action('after_setup_theme', __NAMESPACE__ . '\\remove_admin_bar');