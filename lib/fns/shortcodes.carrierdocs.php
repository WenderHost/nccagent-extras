<?php

namespace NCCAgent\shortcodes\carrierdocs;

/**
 * Displays html which links to the Carrier Docuements Library.
 *
 * @return     string HTML linking to the Carrier Documents Library.
 */
function carrierdocs(){
  $carrierproduct = get_query_var( 'carrierproduct' );

  global $post;
  if( 'carrier' != get_post_type( $post ) )
    return ncc_get_alert(['title' => 'Not a Carrier CPT', 'description' => 'This shortcode only works with the Carrier custom post type.']);

  $template = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/carrierdocs.html' );
  $search = [ '{{carrier}}', '{{carrierdocslink}}' ];
  if( is_user_logged_in() ){
    $carrierdocslink = \NCCAgent\dirlister\dirlister_button();
  } else {
    $carrierdocslink = ncc_get_template('agent-docs-login-or-register');
  }
  $replace = [ $post->post_title, $carrierdocslink ];

  $html = str_replace( $search, $replace, $template );

  return $html;
}
add_shortcode( 'carrierdocs', __NAMESPACE__ . '\\carrierdocs' );