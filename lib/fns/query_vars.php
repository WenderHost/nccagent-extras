<?php

namespace NCCAgent\query_vars;

/**
 * Add `productcarrier` as a query variable.
 *
 * @param      array  $vars   The query variables
 *
 * @return     array  Modified array of query variables.
 */
function add_query_vars( $vars ){
  $vars[] = 'productcarrier';
  return $vars;
}
add_filter( 'query_vars', __NAMESPACE__ . '\\add_query_vars' );

/**
 * Add rewrite which contains the carrier after the product slug/name
 */
function add_rewrites(){
  ncc_error_log('Adding rewrites...');
  add_rewrite_tag( '%productcarrier%', '([0-9a-zA-Z_\-]+)' );
  add_rewrite_rule( 'product/([0-9a-zA-Z_\-]+)/([0-9a-zA-Z_\-]+)/?', 'index.php?post_type=product&name=$matches[1]&productcarrier=$matches[2]', 'top' );
}
add_action( 'init', __NAMESPACE__ . '\\add_rewrites', 10, 0 );