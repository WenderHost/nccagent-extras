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
 * Version:           4.5.0
 *
 * @package           Nccagent_Extras
 */
$css_dir = ( stristr( home_url(), '.local' ) || SCRIPT_DEBUG )? 'css' : 'dist' ;
define( 'NCC_CSS_DIR', $css_dir );
define( 'NCC_DEV_ENV', stristr( home_url(), '.local' ) );
define( 'NCC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NCC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Use composer autoloader
require_once('vendor/autoload.php');

// Include required files
require_once( 'lib/fns/acf-json-save-point.php' );
require_once( 'lib/fns/admin-bar.php' );
require_once( 'lib/fns/admin-custom-columns.php' );
require_once( 'lib/fns/admin-products-import-export.php' );
require_once( 'lib/fns/activecampaign.php' );
require_once( 'lib/fns/breadcrumbs.php' );
require_once( 'lib/fns/csg.php' );
require_once( 'lib/fns/dir-lister.php' );
require_once( 'lib/fns/enqueues.php' );
require_once( 'lib/fns/gettext.php' );
require_once( 'lib/fns/gravityforms.php' );
require_once( 'lib/fns/handlebars.php' );
//require_once( 'lib/fns/hubspot.php' );
require_once( 'lib/fns/marketers.php' );
require_once( 'lib/fns/misc.php' );
require_once( 'lib/fns/options-page.php' );
require_once( 'lib/fns/query_vars.php' );
require_once( 'lib/fns/quick_links.php' );
require_once( 'lib/fns/rest-api.php' );
require_once( 'lib/fns/rest-api.productimport.php' );
require_once( 'lib/fns/shortcodes.php' );
require_once( 'lib/fns/shortcodes.carrierdocs.php' );
require_once( 'lib/fns/shortcodes.carrier_page.php' );
require_once( 'lib/fns/shortcodes.contracting-confirmation.php' );
require_once( 'lib/fns/shortcodes.product_page.php' );
require_once( 'lib/fns/product_by_state_selector.php' );
require_once( 'lib/fns/user-profiles.php' );
require_once( 'lib/fns/utilities.php' );
require_once( 'lib/fns/wp_head.php' );
require_once( 'lib/fns/wp-login.php' );
require_once( 'lib/fns/wp_nav_menus.php' );
require_once( 'lib/fns/yoast.php' );

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
 * ACTIVECAMPAIGN API KEY WARNING
 *
 * Adds a warning if no ACTIVECAMPAIGN_API_KEY found in wp-config.php
 */
if( ! defined( 'ACTIVECAMPAIGN_API_KEY' ) ){
  add_action( 'admin_notices', function(){
    $class = 'notice notice-error';
    $message = __( 'Missing ACTIVECAMPAIGN_API_KEY. Please add your ActiveCampaign API Key to wp-config.php like so: <code>define( \'ACTIVECAMPAIGN_API_KEY\', \'__YOUR_KEY_GOES_HERE__\');</code>.', 'nccagent-extras' );
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
  } );
}

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
