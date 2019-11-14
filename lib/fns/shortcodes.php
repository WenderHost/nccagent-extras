<?php

namespace NCCAgent\shortcodes;

/**
 * Lists Carrier > Products in an accordion
 *
 * @param      array  $atts{
 *  @type   int   $post_id  The post ID.
 * }
 *
 * @return     string  HTML for the Carrier > Products accordion
 */
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

  if( 3 > count( $products ) ){
    foreach( $products as $product ){
      $product_title = ( ! empty( $product['product_details']['alternate_product_name'] ) )? $product['product_details']['alternate_product_name'] : $product['product']->post_title ;
      $states = ( is_array( $product['product_details']['states'] ) )? implode(', ', $product['product_details']['states'] ) : $product['product_details']['states'] ;
      $html.= '<h4 class="product-title">' . $product_title . '</h4><p><code>' . $states . '</code></p>';
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

/**
 * Displays a list of Carriers for a Product
 *
 * @param      array  $atts{
 *    @type   int   $post_id  The post ID.
 * }
 *
 * @return     string  HTML list of Carriers for the Product
 */
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
  $html.= '<ul class="carriers">';

  foreach( $carriers as $post ){
    setup_postdata( $post );
    $logo = get_the_post_thumbnail_url( $post, 'full' );
    if( ! $logo || empty( $logo ) )
      $logo = plugin_dir_url( __FILE__ ) . '../img/placeholder_logo_800x450.png';
    $html.= '<li><a href="' . get_the_permalink( $post_id ) . $post->post_name . '"><img src="' . $logo . '" alt="' . get_the_title() . '" /></a><h3><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h3></li>';
  }
  $html.= '</ul>';

  return $html;
}
add_shortcode( 'acf_product_carriers', __NAMESPACE__ . '\\acf_get_product_carriers' );

function productbycarrier(){
  global $post;

  $carrier_name = sanitize_title_with_dashes( get_query_var( 'productcarrier' ) );
  $args = [
    'name'        => $carrier_name,
    'post_type'   => 'carrier',
    'post_status' => 'publish',
    'numberposts' => 1,
  ];
  $carrier = get_posts($args);
  if( $carrier ){
    $carrier = $carrier[0];
    $products = get_field( 'products', $carrier->ID );
    if( have_rows( 'products', $carrier->ID ) ){
      while( have_rows( 'products', $carrier->ID ) ): the_row();
        $product = get_sub_field( 'product', true );
        if( $post->ID == $product->ID ){
          $current_product = $product;
          $product_details = get_sub_field('product_details');
          $product_name = ( ! empty( $product_details['alternate_product_name'] ) )? $product_details['alternate_product_name'] : $product->post_title ;
          $product_description = $product_details['description'];
          $product_states = $product_details['states'];

          $states = ( is_array( $product_states ) )? implode(', ', $product_states ) : $product_states ;

          return '<h1>' . $carrier->post_title . ' ' . $product_name . '</h1><p><code>' . $states . '</code></p>' . $product_description;
        }
      endwhile;
    }
    return '<pre>No product details were found for ' . $carrier->post_title . ' &gt; ' . $post->post_title . '</pre>';
  }

  return '<code>$carrier_name = ' . $carrier_name . '</code>';
}
add_shortcode( 'productbycarrier', __NAMESPACE__ . '\\productbycarrier' );

/**
 * Displays Products by State DataTable
 *
 * @param      <type>  $atts   The atts
 *
 * @return     string  ( description_of_the_return_value )
 */
function acf_get_products_by_state( $atts ){
  $args = shortcode_atts([
    'table_id' => 'datatable',
    'table_class' => '',
  ], $atts );

  $carriers_query_args = [
    'posts_per_page'  => -1,
    'post_type'       => 'carrier',
    'orderby'         => 'title',
    'order'           => 'ASC',
  ];



  $carriers_array = get_posts( $carriers_query_args );

  $carriers_data = [];
  $table_rows = [];
  if( $carriers_array ){
    wp_enqueue_script( 'datatables-init' );
    wp_localize_script( 'datatables-init', 'wpvars', [
      'table_id' => $args['table_id'],
      'table_class' => $args['table_class']
    ]);
    wp_enqueue_style( 'datatables' );

    $x = 0;
    foreach( $carriers_array as $carrier ){
      $products = get_field( 'products', $carrier->ID );
      if( $products ){
        $products_array = [];
        foreach( $products as $product ){
          $states = ( is_array( $product['product_details']['states'] ) )? implode(', ', $product['product_details']['states'] ) : $product['product_details']['states'] ;
          $products_array[] = [
            'name'      => '<a href="' . get_the_permalink( $product['product']->ID ) . '">' . $product['product']->post_title . '</a>',
            'alt_name'  => $product['product_details']['alternate_product_name'],
            'states'    => $states,
          ];
        }
        $carriers_data[$x]['name'] = '<a href="' . get_the_permalink( $carrier->ID ) . '">' . get_the_title( $carrier->ID ) . '</a>';
        $carriers_data[$x]['products'] = $products_array;
      }
      $x++;
    }
  }

  foreach( $carriers_data as $carrier ) {
    foreach( $carrier['products'] as $product ){
      $table_rows[] = '<tr><td>' . $product['name'] . '</td><td>' . $carrier['name'] . '</td><td>' . $product['states'] . '</td></tr>';
    }
  }

  return '<table class="' . $args['table_class'] . '" id="' . $args['table_id']. '"><thead><tr><th style="width: 30%">Product</th><th style="width: 30%">Carrier</th><th style="width: 40%">States</th></tr></thead><tbody>' . implode( "\n", $table_rows ) . '</tbody></table>';
}
add_shortcode( 'productsbystate', __NAMESPACE__ . '\\acf_get_products_by_state' );

/**
 * Displays the Beamer embed HTML
 *
 * @return     string  The Beamer embed HTML
 */
function display_beamer(){
  $html = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/beamer.html' );
  return $html;
}
add_shortcode( 'beamer', __NAMESPACE__ . '\\display_beamer' );

/**
 * Displays post content while adding a "Read more..." link after a `/more` tag
 *
 * @return     string  HTML content with Read More applied.
 */
function readmore_content(){
  global $post;

  $post_content = $post->post_content;
  if( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() )
    return $post_content;

  $split_content = get_extended( $post_content );
  if( ! empty( $split_content['extended'] ) ){
    $hidetext_js = "\n" . '<script type="text/javascript">hideTextVars = {"carrier": "' . get_the_title( $post->ID ) . '"}</script>';
    $hidetext_js.= "\n" . '<script type="text/javascript">' . file_get_contents( plugin_dir_path( __FILE__ ) . '../js/hidetext.js' ) . '</script>';

    return $split_content['main'] . '<div class="hidetext">' . $split_content['extended'] . '</div>' . $hidetext_js;
  } else {
    return $post_content;
  }
}
add_shortcode( 'readmore_content', __NAMESPACE__ . '\\readmore_content' );