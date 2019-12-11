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


      if( $carriers_array ){

        // DataTables JSON expects an object with a `data` property containing an array of our rows which will be products.
        $products_data = new \stdClass();

        foreach( $carriers_array as $carrier ){
          $products = get_field( 'products', $carrier->ID );
          if( $products ){
            $products_array = [];
            foreach( $products as $product ){

              // Get our array of `states`, sort them alphabetically, and format them as a string of HTML `chiclets`:
              $states = $product['product_details']['states'];
              if( is_array( $states ) )
                sort( $states );
              $states = ( is_array( $states ) )? '<span class="chiclet">' . implode('</span> <span class="chiclet">', $states ) . '</span>' : $states ;
              $states = $states;

              // Set $product_title to not be empty so that this product always has an `alt_name`:
              $product_title = ( ! empty( $product['product_details']['alternate_product_name'] ) )? $product['product_details']['alternate_product_name'] : $product['product']->post_title ;

              $products_data->data[] = [
                'product' => [
                  'alt_name' => $product_title,
                  'name'     => $product['product']->post_title,
                  'url'      => get_the_permalink( $product['product']->ID ) . $carrier->post_name,
                ],
                'carrier' => [
                  'name'  => get_the_title( $carrier->ID ),
                  'url'   => get_the_permalink( $carrier->ID )
                ],
                'states'      => $states,
                'description' => $product['product_details']['description'],
              ];
            }
          }

        }
        wp_send_json( $products_data );
      } // END if( $carriers_array )
      return new \WP_Error('noproducts', __('No products found!') );
    }
  ]);
}
add_action('rest_api_init', __NAMESPACE__ . '\\products_rest_api' );