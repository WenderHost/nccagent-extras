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

// Include required files
require_once( 'lib/fns/enqueues.php' );
require_once( 'lib/fns/shortcodes.php' );
require_once( 'lib/fns/wp_nav_menus.php' );
require_once( 'lib/fns/query_vars.php' );

function ncc_error_log( $message = null ){
  static $counter = 1;

  $bt = debug_backtrace();
  $caller = array_shift( $bt );

  if( 1 == $counter )
    error_log( "\n\n" . str_repeat('-', 25 ) . ' STARTING DEBUG [' . date('h:i:sa', current_time('timestamp') ) . '] ' . str_repeat('-', 25 ) . "\n\n" );
  error_log( "\n" . $counter . '. ' . basename( $caller['file'] ) . '::' . $caller['line'] . "\n" . $message . "\n---\n" );
  $counter++;
}
