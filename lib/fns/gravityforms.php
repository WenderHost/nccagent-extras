<?php

namespace NCCAgent\gravityforms;

/**
 * Modifies the on-screen confirmation that appears after the user submits a GF form.
 *
 * @param      string  $confirmation  The confirmation
 * @param      object  $form          The form
 * @param      object  $entry         The entry
 *
 * @return     string  The filtered confirmation message
 */
function custom_confirmation( $confirmation, $form, $entry ){
  $form_title = $form['title'];
  switch( $form_title ){
    case 'Online Contracting':
      $confirmation = get_online_contracting_message( $confirmation, $form, $entry );

      // I've added #gf_2 to the below alert b/c when viewing the browser console
      // I found that GravityForms was adding some JS to scroll to the top of #gf_2.
      $confirmation = '<div class="alert alert-info" id="gf_2">' . $confirmation . '</div>';
      break;

    default:
      $confirmation.= "\n<!-- No custom confirmation for `$form_title` form. -->";
  }

  return $confirmation;
}
add_filter( 'gform_confirmation', __NAMESPACE__ . '\\custom_confirmation', 10, 3 );

/**
 * Gets the online contracting message.
 *
 * @param      string   $message  The message
 * @param      array    $form     The form
 * @param      entry    $entry    The entry
 * @param      boolean  $oembed   TRUE to oembed the video walk-thru
 *
 * @return     string   The online contracting message.
 */
function get_online_contracting_message( $message, $form, $entry, $oembed = true ){
  $showSureLC = false;
  $sureLCCarriers = [];
  $showStandard = false;
  $standardCarriers = [];

  $message = strip_tags( $message, '<h2><p><h3><h4><h5><h6><ol><ul><li>');

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
  if( $showStandard ){
    // Get our ACF "Online Contracting Settings" options
    $standard_instructions = get_field('standard_instructions','option');

    // Build our Standard Carrier list
    $standard_carrier_list = '<ul><li>' . implode( '</li><li>', $standardCarriers ) . '</li></ul>';

    $message.= str_replace(['{carrier_list}'], [$standard_carrier_list], $standard_instructions);
  }
  if( $showSureLC ){
    // Get our ACF "Online Contracting Settings" options
    $surelc_instructions = get_field('surelc_instructions','option');
    $surelc_walkthru_video = get_field('surelc_walkthru_video','option');
    $video_embed = wp_oembed_get( $surelc_walkthru_video, ['height' => 330] );
    if( ! $video_embed || ! $oembed )
      $video_embed = '<a href="' . $surelc_walkthru_video . '" target="_blank">SureLC Walk-Thru Video</a>';

    // Build our SureLC Carrier list
    $surelc_carrier_list = '<ul><li>' . implode( '</li><li>', $sureLCCarriers ) . '</li></ul>';

    $message.= str_replace(['{carrier_list}','{walkthru_video}'], [$surelc_carrier_list,$video_embed], $surelc_instructions);
  }

  return $message;
}

/**
 * Modifies the Online Contracting form notification.
 *
 * @param      array   $notification  The notification
 * @param      array   $form          The form
 * @param      array   $entry         The entry
 *
 * @return     array  The notification.
 */
function modify_notification( $notification, $form, $entry ){
  $form_title = $form['title'];
  switch( $form_title ){
    case 'Online Contracting':
      switch( $notification['name'] ){
        case 'Online Contracting Confirmation':
          $notification['message'] = get_online_contracting_message( $notification['message'], $form, $entry, false );
          $notification['message'] = '<style type="text/css">.message-body, .message-body *{font-family: Arial, sans-serif;}</style><div class="message-body">' . $notification['message'] . '</div>';
          break;

        case 'Online Contracting Request':
          $total_choices = count( $form['fields'][GF_CARRIER_CHECKLIST_FIELD_ID]['choices'] );
          $carriers = [];
          for( $x = 1; $x <= $total_choices; $x++ ){
            $entry_key = GF_CARRIER_CHECKLIST_FIELD_ID . '.' . $x;
            if( array_key_exists( $entry_key, $entry ) && ! empty( $entry[$entry_key] ) ){
              $carriers[] = explode('|', $entry[$entry_key] );
            }
          }
          if( 0 < count( $carriers ) ){
            $html = '<h3><font style="font-family: sans-serif; font-size: 16px; font-weight: bold;">Selected Carrier(s):</font></h3><table width="99%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EAEAEA"><tbody><tr><td><table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#ffffff">';
            $html.= '<tbody><tr bgcolor="#EAF2FA"><td><font style="font-family: sans-serif; font-size: 12px; font-weight: bold;">Carrier</font></td><td><font style="font-family: sans-serif; font-size: 12px; font-weight: bold;">Type</font></td></tr>';
            $x = 1;
            foreach( $carriers as $carrier ){
              $bgcolor = ( $x % 2 )? '#fff' : '#ededed';
              $html.= '<tr bgcolor="' . $bgcolor . '"><td><font style="font-family: sans-serif; font-size: 12px">' . $carrier[0] . '</font></td><td><font style="font-family: sans-serif; font-size: 12px">' . $carrier[1] . '</font></td></tr>';
              $x++;
            }
            $html.= '</tbody></table></td></tr></table>';
            $notification['message'].= $html;
          }
          break;

        default:
          $notification['message'] = '<p><font style="font-family: sans-serif; font-size: 12px">No notification defined for "' . $notification['name'] . '".</font></p>';
      }
      break;

    default:
      // nothing
  }

  return $notification;
}
add_action( 'gform_notification', __NAMESPACE__ . '\\modify_notification', 10, 3 );

/**
 * Populates the `Select Carrier(s)` checkbox field of our Online Contracting form
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
function populate_select_carriers_checkbox( $form ) {
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

        if( 'aetna' == strtolower( $carrier->post_title ) ){
          $choices[] = [ 'text' => 'Aetna MA/SilverScript', 'value' => 'Aetna MA/SilverScript|Standard' ];
          $inputs[] = [ 'label' => 'Aetna MA/SilverScript', 'id' => "{$field_id}.{$input_id}" ];
          //skipping index that are multiples of 10 (multiples of 10 create problems as the input IDs)
          if ( $input_id % 10 == 0 )
              $input_id++;
          $choices[] = [ 'text' => 'Aetna Supplemental', 'value' => 'Aetna Supplemental|SureLC' ];
          $inputs[] = [ 'label' => 'Aetna Supplemental', 'id' => "{$field_id}.{$input_id}" ];
        } else if( 'cigna' == strtolower( $carrier->post_title ) ){
          $choices[] = [ 'text' => 'Cigna Medicare &ndash; Medicare Advantage Only', 'value' => 'Cigna Medicare &ndash; Medicare Advantage Only|Standard' ];
          $inputs[] = [ 'label' => 'Cigna Medicare &ndash; Medicare Advantage Only', 'id' => "{$field_id}.{$input_id}" ];
          //skipping index that are multiples of 10 (multiples of 10 create problems as the input IDs)
          if ( $input_id % 10 == 0 )
              $input_id++;
          $choices[] = [ 'text' => 'Cigna &ndash; All but Medicare Advantage', 'value' => 'Cigna &ndash; All but Medicare Advantage|SureLC' ];
          $inputs[] = [ 'label' => 'Cigna &ndash; All but Medicare Advantage', 'id' => "{$field_id}.{$input_id}" ];
        } else {
          $online_contracting = get_post_meta( $carrier->ID, 'online_contracting_link', true );
          $choices[] = array( 'text' => $carrier->post_title, 'value' => $carrier->post_title . '|' . $online_contracting );
          $inputs[] = array( 'label' => $carrier->post_title, 'id' => "{$field_id}.{$input_id}" );
        }

        $input_id++;
      }
    }

    $field->choices = $choices;
    $field->inputs = $inputs;

  }

  return $form;
}
if( defined( 'GF_ONLINE_CONTRACTING_FORM_ID' ) ){
  add_filter( 'gform_pre_render_' . GF_ONLINE_CONTRACTING_FORM_ID, __NAMESPACE__ . '\\populate_select_carriers_checkbox' );
  add_filter( 'gform_pre_validation_' . GF_ONLINE_CONTRACTING_FORM_ID, __NAMESPACE__ . '\\populate_select_carriers_checkbox' );
  add_filter( 'gform_pre_submission_filter_' . GF_ONLINE_CONTRACTING_FORM_ID, __NAMESPACE__ . '\\populate_select_carriers_checkbox' );
  add_filter( 'gform_admin_pre_render_' . GF_ONLINE_CONTRACTING_FORM_ID, __NAMESPACE__ . '\\populate_select_carriers_checkbox' );
}