<?php

namespace NCCAgent\query_vars;

/**
 * Add `carrierproduct`, `productcarrier`, and `path` as a query variables.
 *
 * @param      array  $vars   The query variables
 *
 * @return     array  Modified array of query variables.
 */
function add_query_vars( $vars ){
  $vars[] = 'productcarrier';
  $vars[] = 'path';
  $vars[] = 'carrierproduct';
  return $vars;
}
add_filter( 'query_vars', __NAMESPACE__ . '\\add_query_vars' );

/**
 * Add rewrites:
 *
 * - /product/${product}/${carrier}
 * - /carrier/${carrier}/${product}
 */
function add_rewrites(){
  add_rewrite_tag( '%productcarrier%', '([0-9a-zA-Z_\-]+)' );
  add_rewrite_tag( '%carrierproduct%', '([0-9a-zA-Z_\-]+)' );
  add_rewrite_rule( 'product/([0-9a-zA-Z_\-]+)/([0-9a-zA-Z_\-]+)/?', 'index.php?post_type=product&name=$matches[1]&productcarrier=$matches[2]', 'top' );
  add_rewrite_rule( 'carrier/([0-9a-zA-Z_\-]+)/([0-9a-zA-Z_\-]+)/?', 'index.php?post_type=carrier&name=$matches[1]&carrierproduct=$matches[2]', 'top' );
}
add_action( 'init', __NAMESPACE__ . '\\add_rewrites', 10, 0 );

/**
 * Redirect back to the parent Carrier page when we have an invalid $carrierproduct.
 */
function redirect_invalid_carrierproducts(){
  global $post;
  $carrierproduct = sanitize_title_with_dashes( get_query_var( 'carrierproduct' ) );

  if( ! empty( $carrierproduct ) && 'carrier' == get_post_type() ){
    $carrierproduct_exists = false;
    if( have_rows( 'products', $post->ID ) ){
      while( have_rows( 'products' ) ): the_row();
        $product = get_sub_field( 'product' );
        $product_details = get_sub_field( 'product_details' );
        $product_name = ( ! empty( $product_details['alternate_product_name'] ) )? $product_details['alternate_product_name'] : $product->post_title ;
        if( strtolower( sanitize_title_with_dashes( $product_name ) ) == strtolower( $carrierproduct ) )
          $carrierproduct_exists = true;
      endwhile;
    }
    if( ! $carrierproduct_exists ){
      wp_redirect( get_permalink( $post->ID ), 301 );
      exit();
    }
  }
}
add_action( 'template_redirect', __NAMESPACE__ . '\\redirect_invalid_carrierproducts' );