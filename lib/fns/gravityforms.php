<?php

namespace NCCAgent\gravityforms;

function custom_confirmation( $confirmation, $form, $entry ){
  $form_title = $form['title'];
  switch( $form_title ){
    case 'Online Contracting':
      $showSureLC = false;
      $sureLCCarriers = [];
      $showStandard = false;
      $standardCarriers = [];

      $confirmation = '<h2>Submission Complete</h2><p>Thank you for signing up for online contracting. Please follow these instructions below:</p>';

      foreach ($entry as $key => $value) {
        if( GF_CARRIER_CHECKLIST_FIELD_ID == substr( $key, 0, 1 ) && ! empty( $value ) ){
          $data = explode( '|', $value );
          if( 'SureLC' == $data[1] ){
            $sureLCCarriers[] = $data[0];
            $showSureLC = true;
          }
          if( 'Standard' == $data[1] ){
            $standardCarriers[] = $data[0];
            $showStandard = true;
          }
        }
      }
      if( $showSureLC )
        $confirmation.= '<h3>SureLC Instructions</h3><p>The following carriers have streamlined their signup process via our online signup portal: ' . implode( ', ', $sureLCCarriers ) . '</p><p>SureLC sign up instructions go here.</p><hr>';
      if( $showStandard )
        $confirmation.= '<h3>Standard Instructions</h3><p>We will be contacting you to help with the sign up for these carriers: ' . implode( ', ', $standardCarriers ) . '</p><p>Standard sign up instructions go here.</p>';

      $confirmation = '<div class="alert alert-info">' . $confirmation . '</div>';

      //$confirmation.= '<pre>$entry = ' . print_r( $entry, true ) . '</pre>';
      break;

    default:
      $confirmation.= "\n<!-- No custom confirmation for `$form_title` form. -->";
  }

  return $confirmation;
}
add_filter( 'gform_confirmation', __NAMESPACE__ . '\\custom_confirmation', 10, 3 );

/**
 * Populates the checkbox field of our Online Contracting form
 *
 * In order for this form to work, define the following
 * constants in wp-config.php:
 *
 * - GF_ONLINE_CONTRACTING_FORM_ID - ID of the form
 * - GF_CARRIER_CHECKLIST_FIELD_ID - ID of the checklist field
 *
 * @param      object  $form   The form
 *
 * @return     object  Form object
 */
function populate_checkbox( $form ) {
  if( ! defined( 'GF_CARRIER_CHECKLIST_FIELD_ID' ) )
    return $form;

  foreach( $form['fields'] as &$field )  {
    $field_id = GF_CARRIER_CHECKLIST_FIELD_ID;
    if ( $field->id != $field_id )
        continue;

    $carriers_query_args = [
      'posts_per_page'  => -1,
      'post_type'       => 'carrier',
      'orderby'         => 'title',
      'order'           => 'ASC',
    ];
    $carriers_array = get_posts( $carriers_query_args );

    if( $carriers_array ){
      $input_id = 1;
      foreach( $carriers_array as $carrier ){
        //skipping index that are multiples of 10 (multiples of 10 create problems as the input IDs)
        if ( $input_id % 10 == 0 )
            $input_id++;

        $online_contracting = get_post_meta( $carrier->ID, 'online_contracting_link', true );

        //$choices[] = array( 'text' => '<a href="' . get_edit_post_link( $carrier->ID ) . '" target="_blank">' . $carrier->post_title . '</a> (' . $online_contracting . ')', 'value' => $carrier->post_title . ' (' . $online_contracting . ')' );
        $choices[] = array( 'text' => $carrier->post_title, 'value' => $carrier->post_title . '|' . $online_contracting );
        $inputs[] = array( 'label' => $carrier->post_title, 'id' => "{$field_id}.{$input_id}" );

        $input_id++;
      }
    }

    $field->choices = $choices;
    $field->inputs = $inputs;

  }

  return $form;
}
if( defined( 'GF_ONLINE_CONTRACTING_FORM_ID' ) ){
  add_filter( 'gform_pre_render_' . GF_ONLINE_CONTRACTING_FORM_ID, __NAMESPACE__ . '\\populate_checkbox' );
  add_filter( 'gform_pre_validation_' . GF_ONLINE_CONTRACTING_FORM_ID, __NAMESPACE__ . '\\populate_checkbox' );
  add_filter( 'gform_pre_submission_filter_' . GF_ONLINE_CONTRACTING_FORM_ID, __NAMESPACE__ . '\\populate_checkbox' );
  add_filter( 'gform_admin_pre_render_' . GF_ONLINE_CONTRACTING_FORM_ID, __NAMESPACE__ . '\\populate_checkbox' );
}