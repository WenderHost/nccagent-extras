<?php

namespace NCCAgent\shortcodes\confirmation;
use function NCCAgent\gravityforms\{get_online_contracting_message};

/**
 * Displays the Online Contracting sign up confirmation.
 *
 * In order for this shortcode to work, you must set the
 * "Redirect Query String" for the form to be `lid={entry_id}`.
 *
 * @param      array  $atts   No attributes available.
 *
 * @return     string  HTML for the Online Contracting message.
 */
function contracting_confirmation( $atts ){
  $args = shortcode_atts( [
    'foo' => 'bar',
  ], $atts );

  $entry_id = false;
  if( isset( $_GET['lid'] ) && is_numeric( $_GET['lid'] ) )
    $entry_id = $_GET['lid'];

  if( ! $entry_id || ! is_numeric( $entry_id ) )
    return \ncc_get_alert([ 'title' => 'No Form Submission!', 'description' => 'You\'ve accessed this page without submitting the Online Contracting form. Please visit our <a href="' . site_url( 'contracting/contract-online/' ) . '">Online Contracting page</a> to sign up.' ]);

  if( ! class_exists( 'GFAPI' ) )
    return \ncc_get_alert([ 'type' => 'danger', 'title' => 'Missing Form Processing!', 'description' => 'Our form processing does not appear to be working at the moment. Please contact <a href="mailto:' . get_bloginfo('admin_email') . '?subject=Error:+Form+Processing+is+Missing">NCC Support</a>, and alert them of this error.' ]);

  $entry = \GFAPI::get_entry( $entry_id );
  $message = get_online_contracting_message( null, $entry );

  return $message;
}
add_shortcode( 'contracting_confirmation', __NAMESPACE__ . '\\contracting_confirmation' );