<?php
/**
 * NCC Carrier import/export.
 */
class NCC_Carriers_CLI  extends WP_CLI_Command{
  public function __construct(){

  }

  /**
   * Exports Carriers and their Products.
   *
   * Export is delievered as a CSV with these columns:
   *
   * - ID
   * - Carrier
   * - Row_ID
   * - Product
   * - Alternate_Product_Name
   * - States
   *
   * ## OPTIONS
   *
   * [--carrier=<carrier_name>]
   * : Specify a Carrier name to export only that carrier. Optional.
   *
   * @param      <type>  $args        The arguments
   * @param      <type>  $assoc_args  The associated arguments
   */
  public function export( $args, $assoc_args ){

    // Get all Carriers
    $args = [
      'post_type'   => 'carrier',
      'post_status' => 'publish',
      'numberposts' => -1,
      'orderby'     => 'title',
      'order'       => 'ASC',
    ];

    if( isset( $assoc_args['carrier'] ) && ! empty( $assoc_args['carrier'] ) ){
      $carrier = $assoc_args['carrier'];
      \WP_CLI::line('Limiting export Carrier `' . $carrier . '`');
      $args['title'] = $carrier;
    }

    $carriers = get_posts($args);
    if( ! $carriers ){
      $error_message = ( isset( $args['title'] ) )? 'No Carrier found with the name `' . $args['title'] . '`' : 'No Carriers found.' ;
      \WP_CLI::error( $error_message );
    }

    $items = [];
    $counter = 0;
    foreach( $carriers as $carrier ){
      $carrier_columns = [ 'ID' => $carrier->ID, 'Carrier' => $carrier->post_title ];
      if( \have_rows( 'products', $carrier->ID ) ){
        while( \have_rows( 'products', $carrier->ID ) ): the_row();
          $row_id = get_row_index();
          $product = get_sub_field( 'product' );
          $product_details = get_sub_field( 'product_details' );

          $states = ( ! empty($product_details['states']) )? implode(',', $product_details['states'] ) : '';
          $product_name = ( is_object( $product ) )? $product->post_title : '***NO_PRODUCT_FOUND***';

          $product_columns = ['Row_ID' => $row_id, 'Product' => $product_name, 'Alternate_Product_Name' => $product_details['alternate_product_name'], 'States' => $states ];
          $items[] = array_merge( $carrier_columns, $product_columns );
        endwhile;
      } else {
        $items[] = array_merge( $carrier_columns, ['Row_ID' => '', 'Product' => '', 'Alternate_Product_Name' => '', 'States' => '' ] );
      }
    }
    $headers = ['ID','Carrier', 'Row_ID','Product','Alternate_Product_Name','States'];

    // Display the data
    \WP_CLI\Utils\format_items( 'table', $items, $headers );

    // Save the data as a CSV
    $file_timestamp = current_time( 'Y-m-d_Gis');
    $csv_filename = ( isset( $args['title'] ) )? $file_timestamp . '_' . sanitize_title_with_dashes( $args['title'], '', 'save' ) . '-products.csv'  : $file_timestamp . '_carriers-and-products.csv';
    $handle = fopen( $csv_filename, 'w' );
    \WP_CLI\Utils\write_csv( $handle, $items, $headers );
    fclose( $handle );
    \WP_CLI::success('Created ' . $csv_filename );
  }

  /**
   * Imports a CSV of Carriers and Products.
   *
   * ## OPTIONS
   *
   * <filename>
   * : The CSV file we're importing.
   *
   * @param      <type>  $args        The arguments
   * @param      <type>  $assoc_args  The associated arguments
   */
  public function import( $args, $assoc_args ){
    list( $filename ) = $args;
    \WP_CLI::line('🔔 Importing `' . basename($filename) . '`');

    if( ! file_exists($filename) )
      \WP_CLI::error('File does not exist (`' . $filename . '`). Exiting!');

    $items = [];
    if(($h = fopen( $filename, 'r' )) !== FALSE ){
      $total_lines = count( file( $filename ) );
      $x = 0;
      $updated_carrier_products = 0;
      $new_carrier_products = 0;
      $progress = \WP_CLI\Utils\make_progress_bar( '🔔 Updating Carrier > Products > States', $total_lines );
      $row_messages = [];
      while(( $data = fgetcsv( $h, 2048, ',' )) !== FALSE ){
        if( 0 == $x ){
          // Process column headings
          if( ! is_array( $data ) )
            \WP_CLI::error('Strange...are you sure your file is formatted as a CSV?');

          if( 'ID' != $data[0] )
            \WP_CLI::error('Your CSV needs the following header rows:' . "\n" . 'ID,Carrier,Row_ID,Product,Alternate_Product_Name,States');

          $headers = $data;
          \WP_CLI::line('🔔 We are importing with the following columns: ' . implode( ', ', $headers ) );
        } else {
          // Process the row
          $states = $this->_states_to_array( $data[5] );
          $post_id = $data[0];
          $product_name = ( ! empty( $data[4] ) )? $data[4] . ' (' . $data[3] . ')' : $data[3] ;

          // If no Row_ID, create a new row.
          if( empty( $data[2] ) ){
            $row_id = $this->_create_carrier_product( $data );
            if( ! is_wp_error( $row_id ) ){
              $row_messages[] = '🔔 Created product for ' . $data[1] . ' > ' . $product_name ;
              $items[] = [ 'Carrier' => $data[1], 'Product' => $product_name, 'States' => implode( ',', $states ), '✅' => '💡' ];
              $new_carrier_products++;
            } else {
              $row_messages[] = '🚨 ' . $row_id->get_error_message();
            }
            continue;
          }

          $row_id = $data[2] - 1;
          $selector = 'products_' . $row_id . '_product_details_states';

          // Update the field
          $updated = update_field( $selector, $states, $post_id );
          if( $updated ){
            $items[] = [ 'Carrier' => $data[1], 'Product' => $product_name, 'States' => implode( ',', $states ), '✅' => '✅' ];
            $updated_carrier_products++;
          } else {
            $items[] = [ 'Carrier' => $data[1], 'Product' => $product_name, 'States' => implode( ',', $states ), '✅' => '⛔︎' ];
          }
        }
        $progress->tick();
        $x++;
      }
      fclose( $h );
      $progress->finish();
      \WP_CLI\Utils\format_items( 'table', $items, ['Carrier','Product','States','✅'] );
      \WP_CLI::success( 'Import finished with ' . $updated_carrier_products . ' Carrier > Product(s) updated.' );
      if( 0 < $new_carrier_products )
        \WP_CLI::success( $new_carrier_products . ' Carrier > Product(s) created.' );
      \WP_CLI::line( "\n----\nKey:\n✅ Row updated\n⛔︎ Row not updated\n💡 Row created\n----\n" );
      if( ! empty( $row_messages ) ){
        foreach( $row_messages as $message ){
          \WP_CLI::line( $message );
        }
      }
    } else {
      \WP_CLI::error('I\'m unable to open your file.');
    }

  }

  /**
   * Creates a Carrier > Product
   *
   * @param      array  $data   The data
   *
   * @return     int\obj        Returns the Row_ID on success or a WP_Error object on fail.
   */
  private function _create_carrier_product( $data = array() ){
    if( ! is_array( $data ) || empty( $data ) )
      return new \WP_Error('bad_args', __( 'Attempting to create a carrier product with bad arguments. Check your CSV.', 'nccagent' ) );;

    $product_name = $data[3];
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
        'alternate_product_name'  => $data[4],
        'description'             => '',
        'states'                  => $this->_states_to_array( $data[5] ),
      ],
    ];

    if( ! get_post( $data[0] ) )
      return new \WP_Error('no_product_by_id', __( 'The ID in your CSV row does not match any Product IDs in the database.', 'nccagent' ) );

    $row_count = add_row( 'products', $row, $data[0] );
    if( ! $row_count ){
      $product_name = ( ! empty( $data[4] ) )? $data[4] . ' (' . $data[3] . ')' : $data[3] ;
      return new \WP_Error('row_not_added', __( 'Could not add product `' . $data[1] . ' > ' . $product_name . '`.', 'nccagent' ) );
    } else {
      return $row_count;
    }
  }

  /**
   * Converts a CSV string of states to an array.
   *
   * @param      string  $states  The states
   *
   * @return     string  Array of states.
   */
  private function _states_to_array( $states ){
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

    return $states;
  }
}
$nccCarriersCLI = new NCC_Carriers_CLI();
if( class_exists( '\\WP_CLI' ) )
  \WP_CLI::add_command( 'ncc carriers', $nccCarriersCLI );
