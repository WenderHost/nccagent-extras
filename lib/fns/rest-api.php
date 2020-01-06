<?php

namespace NCCAgent\restapi;

/**
 * Provides a directory listing endpoint for the Agent Resource VPN.
 *
 * VPN Root: http://vpn.ncc-agent.com/docs/
 */
function dirlister_rest_api(){
  register_rest_route( 'nccagent/v1', 'dirlister', [
    'methods' => 'GET',
    'permission_callback' => function(){
      if( ! current_user_can('read') ){
        return new \WP_Error('forbidden', __('Only <a href="' . site_url( 'register' ) . '">registered users</a> can access the carrier document library.','nccagent') );
      } else {
        return true;
      }
    },
    'callback' => function( $data ){
      $path = $data->get_param( 'path' );

      if( is_null( $path ) || empty( $path ) )
        return new \WP_Error( 'nopath', __( 'No path provided',  'nccagent' ) );

      $search = ['http://','https://','vpn.ncc-agent.com','agent','docs',' '];
      $replace = ['','','','','','%20'];
      $path = str_replace( $search, $replace, $path );

      // Removing initial slashes from path
      if( '/' == substr( $path, 0, 1 ) )
        $path = ltrim( $path, '/' );

      $path_array = explode('/', trim( $path, '/' ) );

      $fullpath = 'http://vpn.ncc-agent.com/docs/' . $path;
      $contents = @file_get_contents( $fullpath );
      if( ! $contents )
        return new \WP_Error( 'notfound', __( 'No listing found at `' . $fullpath . '`', 'nccagent')  );

      preg_match_all( "/href=[\"'](?<link>.*?)[\"']>(?<text>.*?)<\/a>/i", $contents, $hrefs );

      $links = [];
      if( 0 < count( $hrefs['link'] ) ){
        foreach( $hrefs['link'] as $key => $link ){
          $text = ( '[To Parent Directory]' != $hrefs['text'][$key] )? $hrefs['text'][$key] : '&larr; To Parent Directory' ;
          $links[$key] = ['link' => 'http://vpn.ncc-agent.com' . $link, 'text' => $text ];
          $filetype = wp_check_filetype( basename( $link ) );
          $links[$key]['type'] = ( ! empty( $filetype['ext'] ) )? 'file' : 'dir' ;
        }
      }

      $response = new \stdClass();
      $response->path = $path;
      $response->path_array = $path_array;
      $response->fullpath = $fullpath;
      $response->carrier = str_replace('%20', ' ', ucwords( $path_array[0] ) );
      $response->data = $links;

      wp_send_json( $response, 200 );
    },
  ] );
}
add_action('rest_api_init', __NAMESPACE__ . '\\dirlister_rest_api' );

/**
 * Provides a `Products` endpoint for use in the Plan Finder.
 */
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
                'states'  => $states,
                'product' => [
                  'alt_name' => $product_title,
                  'name'     => $product['product']->post_title,
                  'url'      => get_the_permalink( $product['product']->ID ) . $carrier->post_name,
                ],
                'carrier' => [
                  'name'  => get_the_title( $carrier->ID ),
                  'url'   => get_the_permalink( $carrier->ID )
                ],
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