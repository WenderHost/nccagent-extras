<?php

namespace NCCAgent\restapi\products;

function create_carrier_product( $mapped_data ){
  if( ! is_array( $mapped_data ) || empty( $mapped_data ) )
    return new \WP_Error('bad_args', __( 'Attempting to create a carrier product with bad arguments. Check your CSV.', 'nccagent' ) );;

  $product_name = $mapped_data['product'];
  $product = get_posts([
    'numberposts' => -1,
    'title'       => $product_name,
    'post_type'   => 'product',
  ]);

  if( ! $product )
    return new \WP_Error('no_product', __( 'I could not find a product named `' . $product_name . '`. Check your spelling and update your CSV.', 'nccagent' ) );

  $row = [
    'product'         => $product,
    'product_details' => [
      'alternate_product_name'  => $mapped_data['alternate_product_name'],
      'source_file_name'        => $mapped_data['source_file_name'],
      'source_file_date'        => $mapped_data['source_file_date'],
      'description'             => '',
      'desc_review_date'        => $mapped_data['desc_review_date'],
      'states'                  => $mapped_data['states'],
      'states_review_date'      => $mapped_data['states_review_date'],
      'plan_year'               => $mapped_data['plan_year'],
    ],
  ];

  if( ! get_post( $mapped_data['id'] ) )
    return new \WP_Error('no_product_by_id', __( 'The ID in your CSV row does not match any Product IDs in the database.', 'nccagent' ) );

  $row_count = add_row( 'products', $row, $mapped_data['id'] );
  if( ! $row_count ){
    $product_name = ( ! empty( $mapped_data['alternate_product_name'] ) )? $mapped_data['alternate_product_name'] . ' (' . $mapped_data['product'] . ')' : $data[3] ;
    return new \WP_Error('row_not_added', __( 'Could not add product `' . $mapped_data['carrier'] . ' > ' . $product_name . '`.', 'nccagent' ) );
  } else {
    return $row_count;
  }
}

function products_import_api(){
  register_rest_route( 'nccagent/v1', 'productimport', [
    'methods' => 'POST,GET',
    'permission_callback' => function(){
      return true;
    },
    'callback' => function( $data ){
      $product = $data->get_param('product');

      if( is_null( $product ) || empty( $product ) )
        return new \WP_Error( 'noproduct', __( 'No product provided',  'nccagent' ) );

      foreach ($product as $key => $value) {
        $formatted_key = trim( $key );
        $formatted_key = strtolower( $formatted_key );
        $formatted_key = str_replace(' ', '_', $formatted_key );
        unset($product[$key]);
        $product[$formatted_key] = $value;
        switch( $formatted_key ){
          case 'alternate_product_name':
          case 'alt_product_name_2':
            $product['alternate_product_name'] = ( ! empty( $product['alt_product_name_2'] ) )? $product['alt_product_name_2'] : $product['alternate_product_name'] ;
            break;

          case 'states':
            $states = $value;
            // Remove spaces
            $states = str_replace(' ', '', $states );
            // Remove leading/trailing commas
            $states = trim( $states, ',' );
            // Convert to array
            $states = explode(',', $states );
            // Remove empty elements
            $states = array_filter( $states );
            // Sort array
            sort( $states );
            $product['states'] = $states;
            break;

          default:
            if( stristr( $formatted_key, 'date' ) ){
              if( ! empty( $value ) ){
                $date = date_create( $value );
                $product[$formatted_key] = date_format( $date, 'm/d/Y' );
              } else {
                $product[$formatted_key] = '';
              }
            } else {
              $product[$formatted_key] = $value;
            }
        }
      }

      if( empty( $product['row_id'] ) ){
        $row_id = create_carrier_product( $product );
        if( ! is_wp_error( $row_id ) ){
          wp_send_json( ['row_created' => true], 200 );
        } else {
          wp_send_json( ['error' => true, 'message' => $row_id->get_error_message() ], 200 );
        }
      } else {
        $row_id = $product['row_id'] - 1;
        $row_updated = false;
        $selectors = ['Alternate_Product_Name','Lower_Issue_Age','Upper_Issue_Age','Source_File_Name','Source_File_Date','Desc_Review_Date','States','States_Review_Date','Plan_Year'];
        foreach( $selectors as $selector ){
          $selector = strtolower( $selector );
          $field_name = 'products_' . $row_id . '_product_details_' . $selector;
          $field_updated = update_field( $field_name, $product[$selector], $product['id'] );
          if( $field_updated )
            $row_updated = true;
        }
        wp_send_json( ['row_updated' => $row_updated], 200 );
      }
    }
  ]);
}
add_action('rest_api_init', __NAMESPACE__ . '\\products_import_api' );

/*
function(){
       if( ! current_user_can('activate_plugins') ){
        return new \WP_Error('forbidden', __('Only admins can access this API.','nccagent') );
      } else {
        return true;
      }
    }
 */