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

  if( is_user_logged_in() ){
    $carrierdocslink = \NCCAgent\dirlister\dirlister_button();
  } else {
    $carrierdocslink = ncc_hbs_render_template( 'agent-docs-login-or-register', [ 'home_url' => home_url() ] );
  }

  $html = ncc_hbs_render_template( 'carrierdocs', [ 'carrierdocslink' => $carrierdocslink, 'carrier' => get_the_title( $post ) ] );

  return $html;
}
add_shortcode( 'carrierdocs', __NAMESPACE__ . '\\carrierdocs' );
add_shortcode( 'carrier_docs', __NAMESPACE__ . '\\carrierdocs' );