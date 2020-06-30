<?php

namespace NCCAgent\yoast;

/**
 * Filters SEO titles
 *
 * @param      string  $title  The title
 *
 * @return     string  The filtered title.
 */
function wpseo_title( $title ){
  global $post;
  $posttype = get_post_type( $post );

  switch ( $posttype ) {
    case 'carrier':
      $carrier = get_the_title( $post );
      $carrierproduct = sanitize_title_with_dashes( get_query_var( 'carrierproduct' ) );
      if( ! empty( $carrierproduct ) ){
        $products = get_field( 'products', $post->ID );
        if( have_rows( 'products', $post->ID ) ){
          while( have_rows( 'products' ) ): the_row();
            $product = get_sub_field( 'product' );
            $product_details = get_sub_field( 'product_details' );
            $product_name = ( ! empty( $product_details['alternate_product_name'] ) )? $product_details['alternate_product_name'] : $product->post_title ;
            if( strtolower( sanitize_title_with_dashes( $product_name ) ) == strtolower( $carrierproduct ) ){
              $title = $carrier . ' Contracting: ' . $product_name . ' - NCC';
            }
          endwhile;
        }
      } else {
        $title = $carrier . ' Contracting & Appointment for Agents - NCC';
      }
      break;

    case 'product':
      $product = get_the_title( $post );
      $title = $product . ' FMO for Agent Contracting - NCC';
      break;

    default:
      // nothing
      break;
  }

  return $title;
}
add_filter( 'wpseo_title', __NAMESPACE__ . '\\wpseo_title' );