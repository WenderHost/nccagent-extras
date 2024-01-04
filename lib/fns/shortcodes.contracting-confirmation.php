<?php

namespace NCCAgent\shortcodes\confirmation;
use function NCCAgent\gravityforms\{get_online_contracting_message};

/**
 * Displays the Online Contracting sign up confirmation.
 *
 * In order for this shortcode to work, you must set the
 * "Redirect Query String" for the form to be `lid={entry_id}`.
 *
 * @param      array  $atts   {
 *                    Optional. Array of attributes.
 *
 *             @type  int  $form_id Form ID of the Online Contracting form. Default 2.
 * }
 *
 * @return     string  HTML for the Online Contracting message.
 */
function contracting_confirmation( $atts ){
  $args = shortcode_atts( [
    'form_id' => 2,
  ], $atts );

  if( ! class_exists( 'GFAPI' ) )
    return \ncc_get_alert([ 'type' => 'danger', 'title' => 'Missing Form Processing!', 'description' => 'Our form processing does not appear to be working at the moment. Please contact <a href="mailto:' . get_bloginfo('admin_email') . '?subject=Error:+Form+Processing+is+Missing">NCC Support</a>, and alert them of this error.' ]);

  if( \ncc_is_elementor_edit_mode() && is_numeric( $args['form_id'] ) ){
    $entries = \GFAPI::get_entries( $form_id );
    if( ! is_wp_error( $entries ) ){
      $entry = $entries[0];
    }
  } else {
    $entry_id = false;
    if( isset( $_GET['lid'] ) && is_numeric( $_GET['lid'] ) )
      $entry_id = $_GET['lid'];

    if( ! $entry_id || ! is_numeric( $entry_id ) )
      return \ncc_get_alert([ 'title' => 'No Form Submission!', 'description' => 'You\'ve accessed this page without submitting the Online Contracting form. Please visit our <a href="' . home_url( 'contracting/contract-online/' ) . '">Online Contracting page</a> to sign up.' ]);

    $entry = \GFAPI::get_entry( $entry_id );
  }

  $message = [];
  if( \ncc_is_elementor_edit_mode() )
    $message[] = \ncc_get_alert(['type' => 'info', 'title' => 'Sample Shown Below', 'description' => 'NCC website admins: The below is a sample confirmation taken from the most recent entry for the Online Contracting sign up form:']);

  $message[] = get_online_contracting_message( null, $entry );

  return implode('', $message );
}
add_shortcode( 'contracting_confirmation', __NAMESPACE__ . '\\contracting_confirmation' );