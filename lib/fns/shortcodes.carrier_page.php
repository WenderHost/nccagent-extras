<?php

namespace NCCAgent\shortcodes\carrierpage;

/**
 * Returns the layout for a Carrier CPT page.
 *
 * Yields a different layout depending upon whether or
 * not the `carrierproduct` query_var is set.
 *
 * @return     string  HTML for the Carrier CPT page.
 */
function carrier_page(){
  global $post;
  $carrier = $post;
  $carrierproduct = sanitize_title_with_dashes( get_query_var( 'carrierproduct' ) );

  $post_type = get_post_type();
  if( 'carrier' != $post_type )
    return '';

  $html = [];
  if( ! empty( $carrierproduct ) ){
    $html[] = \NCCAgent\shortcodes\carrierproduct();
    $html[] = \NCCAgent\shortcodes\carrierdocs\carrierdocs();
    $html[] = ncc_quick_links();
  } else {
    $html[] = '<h1>' . get_the_title( $carrier->ID ) . ' Contracting &amp; Appointment</h1>';
    $template = ncc_get_template('contract-online-cta');
    $html[] = '<div style="margin-bottom: 2em;">' . str_replace( '{{contract_online_url}}', site_url( 'contracting/contract-online' ), $template ) . '</div>';
    $html[] = '<div style="margin-bottom: 2em;">' . \NCCAgent\shortcodes\readmore_content() . '</div>';
    $html[] = '<div style="margin-bottom: 2em;">' . \NCCAgent\shortcodes\acf_get_carrier_products([ 'post_id' => $carrier->ID ]) . '</div>';
    $html[] = '<div style="margin-bottom: 2em;">' . ncc_get_template('free-carrier-contracting-kit') . '</div>';
    $html[] = \NCCAgent\shortcodes\carrierdocs\carrierdocs();
  }
  return implode( '', $html );
}
add_shortcode( 'carrier_page', __NAMESPACE__ . '\\carrier_page' );