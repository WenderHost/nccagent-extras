<?php

namespace NCCAgent\activecampaign;

/**
 * Adds ActiveCampaign Tracking code.
 */
function activecampaign_tracking(){
  $html = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/activecampaign.html' );
  echo $html;
}
add_action( 'wp_footer', __NAMESPACE__ . '\\activecampaign_tracking', 9999 );

/**
 * Registers a new user in WordPress and sends the lead to ActiveCampaign
 *
 * @param      object   $record   The form submission object
 * @param      object   $handler  The form handler
 *
 * @return     boolean  Returns `true` when new user is created.
 */
function register_user_and_send_lead_to_activecampaign( $record, $handler ){
  $activecampaign_api_url = 'https://nccagent.api-us1.com/api/3/contacts';

  if( ! defined( 'ACTIVECAMPAIGN_API_KEY' ) ){
    ncc_error_log('🚨 `ACTIVECAMPAIGN_API_KEY` not defined. Please add your ActiveCampaign API Key to `wp-config.php`.');
    return false;
  }

  // Only process the form named `wordpress_and_campaign_registration`:
  $form_name = $record->get_form_settings( 'form_name' );
  if( 'wordpress_and_activecampaign_registration' != $form_name )
    return;

  ncc_error_log('🔔 Processing `wordpress_and_activecampaign_registration`...');

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

  // Format data for ActiveCampaign
  $activecampaign_data = [
    'contact' => [
      'email'       => $fields['email'],
      'firstName'   => $fields['firstname'],
      'lastName'    => $fields['lastname'],
      'phone'       => $fields['phone'],
      'status'      => 0,
      'fieldValues' => [
        [
          'field' => 1,
          'value' => $fields['npn'],
        ],
        [
          'field' => 3,
          'value' => ncc_get_state_name( $fields['state_where_policies_are_sold'] ),
        ],
        [
          'field' => 11,
          'value' => $fields['message'],
        ]
      ],
    ],
  ];
  ncc_error_log('🔔 $activecampaign_data = ' . print_r( $activecampaign_data, true ) );

  // Validate our data
  if( ! is_email( $fields['email'] ) ){
    \ncc_error_log('🚨 `email` is not an email! Exiting...');
    return false;
  }
  if( email_exists( $fields['email'] ) ){
    $handler->messages = [
      'error' => ['Registration not sent. A user with that email address already exists.'],
    ];
    return false;
  }

  // Submit the form to ActiveCampaign
  $response = wp_remote_post(
    $activecampaign_api_url,
    [
      'headers' => [
        'Api-Token' => ACTIVECAMPAIGN_API_KEY,
      ],
      'body'  => json_encode( $activecampaign_data ),
    ]
  );

  // Add the user to WordPress
  if( ! email_exists( $fields['email'] ) && ! username_exists( $fields['npn'] ) ){
    $user_id = wp_insert_user([
      'user_pass' => wp_generate_password( 8, false ),
      'user_login' => $fields['npn'],
      'user_email' => $fields['email'],
      'display_name' => $fields['firstname'],
      'first_name' => $fields['firstname'],
      'last_name' => $fields['lastname'],
    ]);
    add_user_meta( $user_id, 'npn', $fields['npn'], true );
    add_user_meta( $user_id, 'company', $fields['company'], true );
    \NCCAgent\userprofiles\create_user_message( $user_id );
    return true;
  } else {
    ncc_error_log('🔔 A user with the email `' . $fields['email'] . '` or NPN `' . $fields['npn'] . '` already exists!' );
    return false;
  }
}
add_action( 'elementor_pro/forms/new_record', __NAMESPACE__ . '\\register_user_and_send_lead_to_activecampaign', 10, 2 );

/**
 * Validates the User Registration fortm.
 *
 * @param      object  $record   The record
 * @param      object  $handler  The handler
 *
 * @return     bool    Returns `false` if global constants are not in the system.
 */
function validate_user_registration( $record, $handler ){
  if( ! defined( 'ACTIVECAMPAIGN_API_KEY' ) ){
    ncc_error_log('🚨 `ACTIVECAMPAIGN_API_KEY` not defined. Please add your ActiveCampaign API Key to `wp-config.php`.');
    return false;
  }

  // Only process the form named `wordpress_and_activecampaign_registration`:
  $form_name = $record->get_form_settings( 'form_name' );
  if( 'wordpress_and_activecampaign_registration' != $form_name )
    return;

  $email_field = $record->get_field([
    'id' => 'email',
  ]);
  $npn_field = $record->get_field([
    'id' => 'npn',
  ]);

  if( email_exists( $email_field['email']['value'] ) ){
    $handler->add_error( $email_field['email']['id'], 'This email address is already in use in our system.' );
  }
  if( username_exists( $npn_field['npn']['value'] ) ){
    $handler->add_error( $npn_field['npn']['id'], 'That NPN is already registered in our system.' );
  }
}
add_action( 'elementor_pro/forms/validation', __NAMESPACE__ . '\\validate_user_registration', 10, 2 );