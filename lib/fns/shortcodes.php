<?php

namespace NCCAgent\shortcodes;

function acf_get_carrier_products( $atts ){
  $args = shortcode_atts( [
    'post_id' => null,
  ], $atts );

  global $post;
  $post_id = ( is_null( $args['post_id'] ) )? $post->ID : $args['post_id'] ;

  $products = get_field( 'products' );
  if( empty( $products ) )
    return '';

  $html = '<h3>' . get_the_title( $args['post_id'] ) . ' Policies and State Availability</h3>';
  $html.= '<div class="product-content"><p>These are ' . get_the_title( $args['post_id'] ) . '\'s current policies and state availability for ' . date('Y') . ', as well as information on contracting and appointment.</p></div>';
  //$accordion_css = file_get_contents( plugin_dir_path(__FILE__) . '../css/accordion.css' );
  //$html.= '<style type="text/css">' . $accordion_css . '</style>';

  if( 3 > count( $products ) ){
    foreach( $products as $product ){
      $product_title = ( ! empty( $product['product_details']['alternate_product_name'] ) )? $product['product_details']['alternate_product_name'] : $product['product']->post_title ;
      $html.= '<h4 class="product-title">' . $product_title . '</h4><p><code>' . implode(', ', $product['product_details']['states'] ) . '</code></p>';
      $html.= '<div class="product-content">' . apply_filters( 'the_content', $product['product_details']['description'] ) . '</div>';
    }
  } else {
    $accordion_js = file_get_contents( plugin_dir_path( __FILE__ ) . '../js/accordion.js' );
    $html.= '<script type="text/javascript">' . $accordion_js . '</script>';

    $x = 1;
    foreach( $products as $product ){
      $product_title = ( ! empty( $product['product_details']['alternate_product_name'] ) )? $product['product_details']['alternate_product_name'] : $product['product']->post_title ;
      $html.= '<a href="#' . $product['product']->post_name . '-' . $x . '" class="accordion-toggle">' . $product_title . '</a>';
      $product_description = apply_filters( 'the_content', $product['product_details']['description'] );
      $html.= '<div class="accordion-content" id="' . $product['product']->post_name . '-' . $x . '"><p><code>' . implode(', ', $product['product_details']['states'] ) . '</code></p>' . $product_description . '</div>';
      $x++;
    }
  }

  return $html;
}
add_shortcode( 'acf_carrier_products', __NAMESPACE__ . '\\acf_get_carrier_products' );

function acf_get_product_carriers( $atts ){
  $args = shortcode_atts( [
    'post_id' => null,
  ], $atts );

  global $post;
  $post_id = ( is_null( $args['post_id'] ) )? $post->ID : $args['post_id'] ;

  $carriers = get_field( 'carriers' );
  usort( $carriers, function( $a, $b ){
    return strcmp( $a->post_title, $b->post_title );
  });

  if( empty( $carriers ) )
    return '<p><code>No carriers found for `' . get_the_title( $post_id ) . '`.</code></p>';

  $html = '';

  //$carriers_css = file_get_contents( plugin_dir_path( __FILE__ ) . '../css/carriers.css' );
  //$html.= '<style type="text/css">' . $carriers_css . '</style>';

  $html.= '<ul class="carriers">';

  foreach( $carriers as $post ){
    setup_postdata( $post );
    $logo = get_the_post_thumbnail_url( $post, 'full' );
    if( ! $logo || empty( $logo ) )
      $logo = plugin_dir_url( __FILE__ ) . '../img/placeholder_logo_800x450.png';
    $html.= '<li><a href="' . get_the_permalink() . '"><img src="' . $logo . '" alt="' . get_the_title() . '" /></a><h3><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h3></li>';
  }
  $html.= '</ul>';

  return $html;
}
add_shortcode( 'acf_product_carriers', __NAMESPACE__ . '\\acf_get_product_carriers' );

function acf_get_products_by_state( $atts ){
  $carriers_query_args = [
    'posts_per_page'  => -1,
    'post_type'       => 'carrier',
    'orderby'         => 'title',
    'order'           => 'ASC',
  ];

  $carriers_data = [];

  $carriers_array = get_posts( $carriers_query_args );
  if( $carriers_array ){
    wp_enqueue_script( 'datatables-init' );
    wp_enqueue_style( 'datatables' );

    $x = 0;
    foreach( $carriers_array as $carrier ){
      /*
      $carriers_data[$x] = [
        'name'  => get_the_title( $carrier->ID )
      ];
      */
      $products = get_field( 'products', $carrier->ID );
      if( $products ){
        $products_array = [];
        foreach( $products as $product ){
          $products_array[] = [
            'name'      => $product['product']->post_title,
            'alt_name'  => $product['product_details']['alternate_product_name'],
            'states'    => $product['product_details']['states'],
          ];
        }
        $carriers_data[$x]['name'] = get_the_title( $carrier->ID );
        $carriers_data[$x]['products'] = $products_array;
      }
      $x++;
    }
  }

  foreach( $carriers_data as $carrier ) {
    foreach( $carrier['products'] as $product ){
      $table_rows[] = '<tr><td>' . $product['name'] . '</td><td>' . $carrier['name'] . '</td><td>' . implode( ', ', $product['states'] ) . '</td></tr>';
    }
  }

  return '<table id="datatable"><thead><tr><th style="width: 25%">Product</th><th style="width: 25%">Carrier</th><th style="width: 50%">States</th></tr></thead><tbody>' . implode( "\n", $table_rows ) . '</tbody></table>';

  //return '<pre>' . print_r( $carriers_data, true ) . '</pre>';
}
add_shortcode( 'productsbystate', __NAMESPACE__ . '\\acf_get_products_by_state' );