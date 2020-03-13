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
    ncc_error_log('🔔 `HS_PORTAL_ID` not defined. Please add your Hubspot Portal ID to `wp-config.php`.');
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
   * We find the HubSpot Form ID when we edit a form on HubSpot. In this case, we
   * are submitting to the `Agent Registration` form. The Edit URL for that form is:
   *
   * https://app.hubspot.com/forms/2735322/editor/748288ad-0f33-4164-a46b-e394ef7ca982/edit/form
   */
  $hs_form_id = '748288ad-0f33-4164-a46b-e394ef7ca982';

  // Submit the form to HubSpot
  $response = wp_remote_post(
    'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL_ID . '/' . $hs_form_id,
    [ 'body' => $fields ]);

  // Add the user to WordPress
  if( ! email_exists( $fields['email'] ) ){
    $user_id = wp_insert_user([
      'user_pass' => $password,
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