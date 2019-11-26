<?php

namespace NCCAgent\salesforce;

function process_new_record( $record, $handler ){
  if( ! defined( 'HS_PORTAL_ID' ) ){
    ncc_error_log('🔔 `HS_PORTAL_ID` not defined. Please add your Hubspot Portal ID to `wp-config.php`.');
    return false;
  }

  $form_name = $record->get_form_settings( 'form_name' );

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

  // Add HS Context variables
  $hs_context = [];
  if( isset( $_COOKIE['hubspotutk'] ) )
    $hs_context['hutk'] = $_COOKIE['hubspotutk'];
  $hs_context['ipAddress'] = $_SERVER['REMOTE_ADDR'];
  if( isset( $postId ) && is_numeric( $postId ) ){
    $hs_context['pageUrl'] = get_permalink( $postId );
    $hs_context['pageName'] = get_the_title( $postId );
  }
  $fields['hs_context'] = json_encode( $hs_context );

  // Process the submission
  switch( $form_name ){
    case 'wordpress_and_hubspot_registration':

      $args = ['body' => $fields];
      $response = wp_remote_post('https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL_ID . '/748288ad-0f33-4164-a46b-e394ef7ca982', $args );
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
      } else {
        ncc_error_log('🔔 A user with the email `' . $fields['email'] . '` already exists!' );
      }
      break;

    default:
      \ncc_error_log('🔔 No logic set for form `' . $form_name . '`.');
  }
}
add_action( 'elementor_pro/forms/new_record', __NAMESPACE__ . '\\process_new_record', 10, 2 );