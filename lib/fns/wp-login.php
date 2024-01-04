<?php

namespace NCCAgent\wplogin;

function disable_password_strength_meter(){
  wp_dequeue_script('password-strength-meter');
  wp_dequeue_script('user-profile');
  wp_deregister_script('user-profile');

  $suffix = SCRIPT_DEBUG ? '' : '.min';
  wp_enqueue_script( 'user-profile', "/wp-admin/js/user-profile$suffix.js", ['jquery', 'wp-util'], false, 1 );
}
add_action('login_enqueue_scripts', __NAMESPACE__ . '\\disable_password_strength_meter' );

/**
 * Adds our custom CSS with the NCC logo to the WP Login screen.
 */
function login_scripts(){
  wp_enqueue_style('ncc-login', plugin_dir_url( __FILE__ ) . '../' . NCC_CSS_DIR . '/login.css' );
}
add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\\login_scripts' );


/**
 * Redirects visitors to `wp-login.php?action=register` to
 * `site.com/register`
 */
function catch_register()
{
    wp_redirect( home_url( '/register' ) );
    exit(); // always call `exit()` after `wp_redirect`
}
add_action( 'login_form_register', __NAMESPACE__ . '\\catch_register' );

/**
 * Redirects the user upon login
 *
 * @return     string  User dashboard URL
 */
function dashboard_redirect() {
  return '/dashboard';
}
add_filter('login_redirect', __NAMESPACE__ . '\\dashboard_redirect');

/**
 * Filters the login URL.
 *
 * @since 2.8.0
 * @since 4.2.0 The `$force_reauth` parameter was added.
 *
 * @param string $login_url    The login URL. Not HTML-encoded.
 * @param string $redirect     The path to redirect to on login, if supplied.
 * @param bool   $force_reauth Whether to force reauthorization, even if a cookie is present.
 *
 * @return string
 */
function custom_login_url( $login_url, $redirect, $force_reauth ){
    // This will append /custom-login/ to you main site URL as configured in general settings (ie https://domain.com/custom-login/)
    $login_url = home_url( '/dashboard/' );

    if ( ! empty( $redirect ) )
        $login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );

    if ( $force_reauth )
        $login_url = add_query_arg( 'reauth', '1', $login_url );

    return $login_url;
}
//add_filter( 'login_url', __NAMESPACE__ . '\\custom_login_url', 10, 3 );