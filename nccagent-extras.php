<?php
/**
 * Plugin Name:       NCCAgent Extras
 * Plugin URI:        https://github.com/WenderHost/nccagent-extras
 * GitHub Plugin URI: https://github.com/WenderHost/nccagent-extras
 * Description:       Additional helpers for the NCCAgent.com website.
 * Author:            Michael Wender
 * Author URI:        https://mwender.com
 * Text Domain:       nccagent-extras
 * Domain Path:       /languages
 * Version:           1.0.3
 *
 * @package           Nccagent_Extras
 */
$css_dir = ( stristr( site_url(), '.local' ) || SCRIPT_DEBUG )? 'css' : 'dist' ;
define( 'NCC_CSS_DIR', $css_dir );

// Include required files
require_once( 'lib/fns/admin-bar.php' );
require_once( 'lib/fns/csg.php' );
require_once( 'lib/fns/enqueues.php' );
require_once( 'lib/fns/shortcodes.php' );
require_once( 'lib/fns/wp-login.php' );
require_once( 'lib/fns/wp_nav_menus.php' );
require_once( 'lib/fns/query_vars.php' );
require_once( 'lib/fns/salesforce.php' );
require_once( 'lib/fns/user-profiles.php' );

/**
 * Don't send the `Login Details` notification email.
 */
if ( !function_exists( 'wp_new_user_notification' ) ) :
  function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
    return;
  }
endif;

/**
 * Enhanced logging
 *
 * @param      string  $message  The message
 */
function ncc_error_log( $message = null ){
  static $counter = 1;

  $bt = debug_backtrace();
  $caller = array_shift( $bt );

  if( 1 == $counter )
    error_log( "\n\n" . str_repeat('-', 25 ) . ' STARTING DEBUG [' . date('h:i:sa', current_time('timestamp') ) . '] ' . str_repeat('-', 25 ) . "\n\n" );
  error_log( "\n" . $counter . '. ' . basename( $caller['file'] ) . '::' . $caller['line'] . "\n" . $message . "\n---\n" );
  $counter++;
}
