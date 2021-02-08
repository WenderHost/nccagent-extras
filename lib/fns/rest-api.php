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
        return new \WP_Error('forbidden', __('Only <a href="' . site_url( 'login' ) . '">registered users</a> can access the carrier document library.','nccagent') );
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
      $path_array = array_map( function( $item ){
        $words = explode( '%20', $item );
        foreach( $words as $key => $word ){
          if( 3 <= strlen( $word ) ){
            switch( strtolower( $word ) ){
              case 'csi':
              case 'cso':
                $words[$key] = $word;
                break;

              default:
                $words[$key] = ucfirst( strtolower( $word ) );
            }

          } else if( in_array( strtolower( $word ), ['of','by'] ) ){
            $words[$key] = $word;
          } else {
            $words[$key] = strtoupper( $word );
          }
        }
        return implode( '%20', $words );
      }, $path_array );


      $fullpath = 'https://vpn.ncc-agent.com/docs/' . $path;
      $contents = @file_get_contents( $fullpath );
      if( ! $contents )
        return new \WP_Error( 'notfound', __( 'No listing found at `' . $fullpath . '`', 'nccagent')  );

      /**
       * Find all hrefs in the directory listing from the VPN,
       * and split them into the `link` and the `text`:
       */
      preg_match_all( "/href=[\"'](?<link>.*?)[\"']>(?<text>.*?)<\/a>/i", $contents, $hrefs );

      $links = [];
      if( 0 < count( $hrefs['link'] ) ){
        foreach( $hrefs['link'] as $key => $link ){
          $link_text = $hrefs['text'][$key];

          // Correct captilization:
          $search = ['AETNA','CIGNA','EON','HUMANA'];
          $replace = ['Aetna','Cigna','Eon Health','Humana'];
          $link_text = str_replace( $search, $replace, $link_text );

          $text = ( '[To Parent Directory]' != $hrefs['text'][$key] )? $link_text : '&larr; Back' ;
          $links[$key] = ['link' => 'https://vpn.ncc-agent.com' . $link, 'text' => $text ];
          $filetype = wp_check_filetype( basename( $link ) );
          $links[$key]['type'] = ( ! empty( $filetype['ext'] ) )? 'file' : 'dir' ;
        }
      }

      $response = new \stdClass();
      $response->path = $path;
      $response->path_array = $path_array;
      $response->fullpath = $fullpath;
      $carrier = str_replace('%20', ' ', $path_array[0] );
      $carrier_words = explode(' ', $carrier );
      foreach ($carrier_words as $key => $word ) {
        if( 3 <= strlen( $word ) ){
          $carrier_words[$key] = ucfirst( strtolower( $word ) );
        } else if( in_array( strtolower( $word ), ['of'] ) ){
          $carrier_words[$key] = $word;
        } else {
          $carrier_words[$key] = strtoupper( $word );
        }
      }
      //$carrier = ucwords( strtolower( $carrier ) );
      $carrier = implode( ' ', $carrier_words );
      $response->carrier = $carrier;
      $response->data = $links;

      wp_send_json( $response, 200 );
    },
  ] );
}
add_action('rest_api_init', __NAMESPACE__ . '\\dirlister_rest_api' );

/**
 * Adds the `orderby=rand` option for REST queries for the `Team Member` CPT Collection.
 *
 * @param      array  $params  The parameters
 *
 * @return     array  The filtered $params
 */
function filter_add_team_member_rest_orderby_params( $params ){
  $params['orderby']['enum'][] = 'rand';
  return $params;
}
add_filter( 'rest_team_member_collection_params', __NAMESPACE__ . '\\filter_add_team_member_rest_orderby_params' );

/**
 * Adds custom fields to REST response for `team_member` CPT.
 */
function register_team_member_fields(){
  register_rest_field( 'team_member', 'team_member_details', [
    'get_callback' => function( ){
      global $post;
      $state = get_query_var( 'state' );
      $name = $post->post_title;
      $title = get_field('title');
      $phone = get_field('phone');
      $email = get_field('email');
      $bio = get_field('bio');

      // Get ACF HubSpot fields
      $marketerFields = get_fields( $post->ID, false );
      $marketerFields['hubspot'] = get_field( 'hubspot', $post->ID );
      $chat_query_parameter = ( ! empty( $marketerFields['hubspot']['chat_query_parameter'] ) )? $marketerFields['hubspot']['chat_query_parameter'] : false ;

      $state_terms = get_the_terms( $post, 'state' );
      $states = [];
      if( $state_terms ){
        foreach( $state_terms as $state ) {
          $states[$state->term_id] = $state->name;
        }
      }
      $photo = ( has_post_thumbnail( $post ) )? get_the_post_thumbnail_url( $post, 'large' ) : plugin_dir_url( __FILE__ ) . '../img/avatar.png' ;
      $permalink = get_the_permalink( $post->ID );
      $name_array = explode( ' ', $name );
      $lastname = array_pop( $name_array );
      $firstname = implode(' ', $name_array );
      $team_member_details = [ 'name' => $name ,'firstname' => $firstname, 'lastname' => $lastname, 'title' => $title, 'phone' => $phone, 'email' => $email, 'bio' => $bio, 'states' => $states, 'photo' => $photo, 'permalink' => $permalink, 'chat_query_parameter' => $chat_query_parameter ];
      return $team_member_details;
    },
    'schema' => [
      'description' => __( 'Team member details.' ),
      'type' => 'array',
    ],
  ]);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_team_member_fields' );

/**
 * Provides a `Products` endpoint for use in the Product Finder.
 */
function products_rest_api(){
  register_rest_route( 'nccagent/v1', 'products', [
    'methods' => 'GET',
    'permission_callback' => function(){
      return true;
    },
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
              if( ! is_object( $product['product'] ) )
                continue;

              // Skip if the Product isn't published
              if( 'publish' != $product['product']->post_status )
                continue;

              // Get our array of `states`, sort them alphabetically, and format them as a string of HTML `chiclets`:
              $states = $product['product_details']['states'];
              $states = ncc_build_state_chiclets( $product['product_details']['states'] );

              // Set $product_title to not be empty so that this product always has an `alt_name`:
              $product_title = ( ! empty( $product['product_details']['alternate_product_name'] ) )? $product['product_details']['alternate_product_name'] : $product['product']->post_title ;

              $products_data->data[] = [
                'product' => [
                  'alt_name' => $product_title,
                  'name'     => $product['product']->post_title,
                  'url'      => get_the_permalink( $carrier->ID ) . sanitize_title_with_dashes( $product_title ) . '/',
                ],
                'carrier' => [
                  'name'  => get_the_title( $carrier->ID ),
                  'url'   => get_the_permalink( $carrier->ID )
                ],
                'lower_issue_age' => $product['product_details']['lower_issue_age'],
                'upper_issue_age' => $product['product_details']['upper_issue_age'],
                'description' => $product['product_details']['description'],
                'desc_review_date' => $product['product_details']['desc_review_date'],
                'states'  => $states,
                'states_review_date' => $product['product_details']['states_review_date'],
                'plan_year' => $product['product_details']['plan_year'],
                'online_contracting_url' => site_url( 'contracting/contract-online/' ),
                'kit_request_url' => site_url( 'contracting/kit-request/' ),
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