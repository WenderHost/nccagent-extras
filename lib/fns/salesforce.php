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
    $fields[$id] = $field['value'];
  }

  // Add HS Context variables
  $hs_context = [];
  if( isset( $_COOKIE['hubspotutk'] ) )
    $hs_context['hutk'] = $_COOKIE['hubspotutk'];
  $hs_context['ipAddress'] = $_SERVER['REMOTE_ADDR'];
  if( isset( $fields['postId'] ) && is_numeric( $fields['postId'] ) ){
    $hs_context['pageUrl'] = get_permalink( $fields['postId'] );
    $hs_context['pageName'] = get_the_title( $fields['postId'] );
    unset( $fields['postId'] );
  }
  $fields['hs_context'] = json_encode( $hs_context );

  // Process the submission
  switch( $form_name ){
    case 'wordpress_and_hubspot_registration':

      $args = ['body' => $fields];
      $response = wp_remote_post('https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL_ID . '/748288ad-0f33-4164-a46b-e394ef7ca982', $args );
      ncc_error_log('👉 $args = ' . print_r( $args, true ) );
      break;

    default:
      \ncc_error_log('🔔 No logic set for form `' . $form_name . '`.');
  }
}
add_action( 'elementor_pro/forms/new_record', __NAMESPACE__ . '\\process_new_record', 10, 2 );