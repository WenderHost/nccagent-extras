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
 * Version:           2.3.2
 *
 * @package           Nccagent_Extras
 */
$css_dir = ( stristr( site_url(), '.local' ) || SCRIPT_DEBUG )? 'css' : 'dist' ;
define( 'NCC_CSS_DIR', $css_dir );
define( 'NCC_DEV_ENV', stristr( site_url(), '.local' ) );

// Include required files
require_once( 'lib/fns/acf-json-save-point.php' );
require_once( 'lib/fns/admin-bar.php' );
require_once( 'lib/fns/admin-custom-columns.php' );
require_once( 'lib/fns/breadcrumbs.php' );
require_once( 'lib/fns/csg.php' );
require_once( 'lib/fns/dir-lister.php' );
require_once( 'lib/fns/enqueues.php' );
require_once( 'lib/fns/gettext.php' );
require_once( 'lib/fns/gravityforms.php' );
require_once( 'lib/fns/hubspot.php' );
require_once( 'lib/fns/marketers.php' );
require_once( 'lib/fns/options-page.php' );
require_once( 'lib/fns/query_vars.php' );
require_once( 'lib/fns/quick_links.php' );
require_once( 'lib/fns/rest-api.php' );
require_once( 'lib/fns/shortcodes.php' );
require_once( 'lib/fns/shortcodes.carrierdocs.php' );
require_once( 'lib/fns/shortcodes.carrier_page.php' );
require_once( 'lib/fns/shortcodes.product_page.php' );
require_once( 'lib/fns/product_by_state_selector.php' );
require_once( 'lib/fns/user-profiles.php' );
require_once( 'lib/fns/utilities.php' );
require_once( 'lib/fns/wp_head.php' );
require_once( 'lib/fns/wp-login.php' );
require_once( 'lib/fns/wp_nav_menus.php' );

function nccagent_cli_init() {
  require_once( 'lib/cli/ncc-carriers.php' );
  require_once( 'lib/cli/ncc-users.php' );

  // Only add the namespace if the required base class exists (WP-CLI 1.5.0+).
  // This is optional and only adds the description of the root `ncc`
  // command.
  require_once( 'lib/cli/class-cli-ncc-command-namespace.php' );
  if ( class_exists( 'WP_CLI\Dispatcher\CommandNamespace' ) )
    WP_CLI::add_command( 'ncc', 'NCCAgent_CLI_NCC_Command_Namespace' );
}
if ( defined( 'WP_CLI' ) && WP_CLI )
  add_action( 'plugins_loaded', 'nccagent_cli_init', 20 );

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
