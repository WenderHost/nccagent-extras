<?php

function ncc_product_by_state_selector(){
  global $post;
  $product = $post;
  $productcarrier = sanitize_title_with_dashes( get_query_var( 'productcarrier' ) );

  wp_enqueue_script('select2');
  $product_finder_page_id = get_field('product_finder_page','option');
  $product_finder_url = get_permalink( $product_finder_page_id );
  $selector = ncc_get_state_options();
  wp_localize_script( 'select2', 'wpvars', ['product' => get_the_title( $product->ID ), 'product_finder_url' => $product_finder_url, 'stateOptionData' => $selector['data'] ] );
  $script = file_get_contents( plugin_dir_path( __FILE__ ). '../js/plan-by-state-selector.js' );
  wp_add_inline_script( 'select2', $script, 'after' );



  $template = ncc_get_template([
    'template' => 'plan-by-state-selector',
    'search'   => [
      '{{product_name}}',
      '{{selector}}',
    ],
    'replace'  => [
      get_the_title( $product->ID ),
      $selector['options'],
    ]
  ]);
  return $template;
}