<?php

namespace NCCAgent\shortcodes\productpage;

/**
 * Returns the layout for a Product CPT page.
 *
 * Yields a different layout depending upon whether or
 * not the `productcarrier` query_var is set.
 *
 * @return     string  HTML for the Carrier CPT page.
 */
function product_page(){
  global $post;
  $product = $post;
  $productcarrier = sanitize_title_with_dashes( get_query_var( 'productcarrier' ) );

  $post_type = get_post_type();
  if( 'product' != $post_type )
    return '';

  $html = [];
  if( ! empty( $productcarrier ) ){
    $html[] = '<div style="margin-bottom: 2em;">' . do_shortcode( '[productbycarrier]' ) . '</div>';
    $html[] = ncc_quick_links();
  } else {
    $html[] = '<h1>' . get_the_title( $product->ID ) . ' FMO for Agent Contracting</h1>';
    $html[] = '<div style="margin-bottom: 2em;">' . get_the_content() . '</div>';
    $html[] = '<div style="margin-bottom: 2em;"><h3>Explore ' . get_the_title( $product->ID ) . ' Products from these Carriers</h3>' . \NCCAgent\shortcodes\acf_get_product_carriers([ 'post_id' => $product->ID ] ) . '</div>';
  }
  return implode( '', $html );
}
add_shortcode( 'product_page', __NAMESPACE__ . '\\product_page' );