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