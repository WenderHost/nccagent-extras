<?php

namespace NCCAgent\salesforce;

/**
 * Adds Hubspot Tracking code.
 */
function hs_tracking(){
  $html = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/hubspot.html' );
  echo $html;
}
add_action( 'wp_footer', __NAMESPACE__ . '\\hs_tracking', 9999 );

/**
 * Registers a new user in WordPress and sends the lead to HubSpot
 *
 * @param      object   $record   The form submission object
 * @param      object   $handler  The form handler
 *
 * @return     boolean  ( description_of_the_return_value )
 */
function register_user_and_send_lead_to_hubspot( $record, $handler ){
  if( ! defined( 'HS_PORTAL_ID' ) ){
    ncc_error_log('🚨 `HS_PORTAL_ID` not defined. Please add your Hubspot Portal ID to `wp-config.php`.');
    return false;
  }
  if( ! defined( 'HS_AGENT_REGISTRATION_FORM_ID' ) || empty( HS_AGENT_REGISTRATION_FORM_ID ) ){
    ncc_error_log('🚨 `HS_AGENT_REGISTRATION_FORM_ID` not defined. Please add the corresponding HubSpot `Agent Registration` form ID to `wp-config.php`.');
    return false;
  }

  // Only process the form named `wordpress_and_hubspot_registration`:
  $form_name = $record->get_form_settings( 'form_name' );
  if( 'wordpress_and_hubspot_registration' != $form_name )
    return;

  // Get our form field values
  $raw_fields = $record->get( 'fields' );
  $fields = [];
  foreach( $raw_fields as $id => $field ){
    switch( $id ){
      case 'password':
      case 'postId':
        $$id = $field['value'];
        break;

      default:
        $fields[$id] = $field['value'];
    }

  }

  // Validate our data
  if( ! is_email( $fields['email'] ) ){
    \ncc_error_log('🚨 `email` is not an email! Exiting...');
    return false;
  }

  // Add HubSpot Context variables
  $hs_context = [];
  if( isset( $_COOKIE['hubspotutk'] ) )
    $hs_context['hutk'] = $_COOKIE['hubspotutk'];
  $hs_context['ipAddress'] = $_SERVER['REMOTE_ADDR'];
  if( isset( $postId ) && is_numeric( $postId ) ){
    $hs_context['pageUrl'] = get_permalink( $postId );
    $hs_context['pageName'] = get_the_title( $postId );
  }
  $fields['hs_context'] = json_encode( $hs_context );

  /**
   * HubSpot Form ID
   *
   * 03/31/2020 (07:07) - moved the HubSpot Form ID to wp-config.php as a
   * defined variable called `HS_AGENT_REGISTRATION_FORM_ID`.
   */

  // Submit the form to HubSpot
  $response = wp_remote_post(
    'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL_ID . '/' . HS_AGENT_REGISTRATION_FORM_ID,
    [ 'body' => $fields ]);

  // Add the user to WordPress
  if( ! email_exists( $fields['email'] ) ){
    $user_id = wp_insert_user([
      'user_pass' => wp_generate_password( 8, false ),
      'user_login' => $fields['email'],
      'user_email' => $fields['email'],
      'display_name' => $fields['firstname'],
      'first_name' => $fields['firstname'],
      'last_name' => $fields['lastname'],
    ]);
    add_user_meta( $user_id, 'npn', $fields['npn'], true );
    return true;
  } else {
    ncc_error_log('🔔 A user with the email `' . $fields['email'] . '` already exists!' );
    return false;
  }
}
add_action( 'elementor_pro/forms/new_record', __NAMESPACE__ . '\\register_user_and_send_lead_to_hubspot', 10, 2 );