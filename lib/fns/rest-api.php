<?php

namespace NCCAgent\restapi;

function products_rest_api(){
  register_rest_route( 'nccagent/v1', 'products', [
    'methods' => 'GET',
    'callback' => function(){
      $carriers_query_args = [
        'posts_per_page'  => -1,
        'post_type'       => 'carrier',
        'orderby'         => 'title',
        'order'           => 'ASC',
      ];

      $carriers_array = get_posts( $carriers_query_args );

      $carriers_data = [];
      $products_data = new \stdClass();
      $table_rows = [];
      if( $carriers_array ){
        $x = 0;
        foreach( $carriers_array as $carrier ){
          $products = get_field( 'products', $carrier->ID );
          if( $products ){
            $products_array = [];
            foreach( $products as $product ){
              $states = $product['product_details']['states'];
              if( is_array( $states ) )
                sort( $states );
              $states = ( is_array( $states ) )? '<span class="chiclet">' . implode('</span> <span class="chiclet">', $states ) . '</span>' : $states ;
              $states = $states;

              $product_title = ( ! empty( $product['product_details']['alternate_product_name'] ) )? $product['product_details']['alternate_product_name'] : $product['product']->post_title ;
  /*
  foreach( $carriers_data as $carrier ) {
    foreach( $carrier['products'] as $product ){
      $table_rows[] = '<tr><td class="details-control">[+]</td><td>' . $product['link'] . '</td><td>' . $carrier['name'] . '</td><td>' . $product['states'] . '</td><td><h4><a href="' . get_the_permalink( $product['ID'] ) . $product['carrier_slug'] . '">' . $product['carrier_name'] . ' ' . $product['name'] . '</a></h4><div class="states">' . $product['states'] . '</div></td></tr>';
    }
  }
  /**/
              $products_data->data[$x] = [
                /*'product'     => '<a href="' . get_the_permalink( $product['product']->ID ) . $carrier->post_name . '">' . $product_title . '</a> <span>' . $product['product']->post_title . '</span>',*/
                'product' => [
                  'alt_name' => $product_title,
                  'name'     => $product['product']->post_title,
                  'url'      => get_the_permalink( $product['product']->ID ) . $carrier->post_name,
                ],
                /*
                'carrier'     => '<a href="' . get_the_permalink( $carrier->ID ) . '">' . get_the_title( $carrier->ID ). '</a>',*/
                'carrier' => [
                  'name'  => get_the_title( $carrier->ID ),
                  'url'   => get_the_permalink( $carrier->ID )
                ],
                'states'      => $states,
                'description' => $product['product_details']['description'],
              ];
              $x++;
            }
          }

        }
        wp_send_json( $products_data );
      } // if( $carriers_array )
      return new \WP_Error('noproducts', __('No products found!') );
    }
  ]);
}
add_action('rest_api_init', __NAMESPACE__ . '\\products_rest_api' );