<?php

namespace NCCAgent\gravityforms;

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