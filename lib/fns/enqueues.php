<?php

namespace NCCAgent\enqueues;

/**
 * Enqueues our scripts and styles.
 */
function enqueue_scripts(){
  // Datatables provides the app framework which powers the Plan Finder
  wp_register_script( 'datatables', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', ['jquery'], '1.10.19', true );

  // We use Select2 for enhanced selects on the Plan Finder
  wp_register_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js', ['jquery'], '4.0.12' );

  // My custom JS which initializes the Plan Finder
  wp_register_script( 'plan-finder', plugin_dir_url( __FILE__ ) . '../js/plan-finder.js', ['datatables','select2'], filemtime( plugin_dir_path( __FILE__ ). '../js/plan-finder.js' ), true );

  wp_enqueue_script( 'dropdown-side-menu', plugin_dir_url( __FILE__ ) . '../js/menu.js', ['jquery'], filemtime( plugin_dir_path( __FILE__ ) . '../js/menu.js' ), true );

  wp_register_script( 'dirlister', plugin_dir_url( __FILE__ ) . '../js/dirlister.js', ['jquery'], filemtime( plugin_dir_path( __FILE__ ) . '../js/dirlister.js' ), true );

  // We currently are locally hosting our Google Fonts, uncomment the following if we want to offload them to the Google CDN:
  //wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Fira+Sans:700,700i&Roboto&display=swap' ); 'google-fonts',

  // Our custom styles
  wp_enqueue_style( 'nccagent-styles', plugin_dir_url( __FILE__ ) . '../' . NCC_CSS_DIR . '/main.css', ['hello-elementor','elementor-frontend'], filemtime( plugin_dir_path( __FILE__ ) . '../' . NCC_CSS_DIR . '/main.css' ) );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts' );