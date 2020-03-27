<?php

namespace NCCAgent\enqueues;

/**
 * Enqueues our scripts and styles.
 */
function enqueue_scripts(){
  // DATATABLES: Datatables provides the app framework which powers the Product Finder
  // Just datatables: //cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js
  // Datatables w/ Responsive: //cdn.datatables.net/v/dt/dt-1.10.20/r-2.2.3/datatables.min.js
  wp_register_script( 'datatables', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', ['jquery'], '1.10.19', true );

  // SELECT2: We use Select2 for enhanced selects on the Product Finder
  wp_register_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js', ['jquery'], '4.0.12' );

  // PRODUCT FINDER: My custom JS which initializes the Product Finder
  wp_register_script( 'product-finder', plugin_dir_url( __FILE__ ) . '../js/product-finder.js', ['datatables','select2'], filemtime( plugin_dir_path( __FILE__ ). '../js/product-finder.js' ), true );

  // DROPDOWN SIDEMENU: Used to display Products/Carriers sidenav
  wp_enqueue_script( 'dropdown-side-menu', plugin_dir_url( __FILE__ ) . '../js/menu.js', ['jquery'], filemtime( plugin_dir_path( __FILE__ ) . '../js/menu.js' ), true );

  // DIRECTORY LISTER: Displays links to files from the NCC VPN.
  wp_register_script( 'dirlister', plugin_dir_url( __FILE__ ) . '../js/dirlister.js', ['jquery'], filemtime( plugin_dir_path( __FILE__ ) . '../js/dirlister.js' ), true );

  // Global JS
  wp_enqueue_script( 'globaljs', plugin_dir_url( __FILE__ ) . '../js/global.js', ['jquery'], filemtime( plugin_dir_path( __FILE__ ) ) . '../js/global.js', true );

  // We currently are locally hosting our Google Fonts, uncomment the following if we want to offload them to the Google CDN:
  //wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Fira+Sans:700,700i&Roboto&display=swap' ); 'google-fonts',

  // Our custom styles
  wp_enqueue_style( 'nccagent-styles', plugin_dir_url( __FILE__ ) . '../' . NCC_CSS_DIR . '/main.css', ['hello-elementor','elementor-frontend'], filemtime( plugin_dir_path( __FILE__ ) . '../' . NCC_CSS_DIR . '/main.css' ) );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts' );

/**
 * Custom styles for the WP Admin
 */
function custom_admin_styles(){
  wp_enqueue_style( 'ncc-admin-styles', plugin_dir_url( __FILE__ ) . '../css/admin.css', null, filemtime( plugin_dir_path( __FILE__ ) . '../css/admin.css' ) );
}
add_action( 'admin_head', __NAMESPACE__ . '\\custom_admin_styles' );