<?php

namespace NCCAgent\wpcli;

/**
 * Tools for working with the NCC website.
 */
class NCC_Cli{
  public function __construct(){

  }

  /**
   * Exports Carriers and their Products.
   *
   * Export is delievered as a CSV with these columns:
   *
   * - ID
   * - Carrier
   * - Product
   * - Alternate_Product_Name
   * - States
   */
  public function export(){

    // Get all Carriers
    $args = [
      'post_type'   => 'carrier',
      'post_status' => 'publish',
      'numberposts' => -1,
      'orderby'     => 'title',
      'order'       => 'ASC',
    ];
    $carriers = get_posts($args);
    if( ! $carriers )
      \WP_CLI::error('No Carriers found!');

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
    $csv_filename = current_time( 'Y-m-d_Gis') . '_carriers-and-products.csv';
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
      $progress = \WP_CLI\Utils\make_progress_bar( '🔔 Updating Carrier > Products > States', $total_lines );
      while(( $data = fgetcsv( $h, 2048, ',' )) !== FALSE ){
        if( 0 == $x ){
          // Process column headings
          if( ! is_array( $data ) )
            \WP_CLI::error('Strange...are you sure your file is formatted as a CSV?');

          if( 'ID' != $data[0] )
            \WP_CLI::error('Your CSV needs the following header row:' . "\n\n" . 'ID,Carrier,Row_ID,Product,Alternate_Product_Name,States');

          $headers = $data;
          \WP_CLI::line('🔔 We are importing with the following columns: ' . implode( ', ', $headers ) );
        } else {
          // Process the row
          $row_id = $data[2] - 1;
          $selector = 'products_' . $row_id . '_product_details_states';
          $states = explode( ',', $data[5] );
          sort($states);
          $post_id = $data[0];
          $product_name = ( ! empty( $data[4] ) )? $data[4] . ' (' . $data[3] . ')' : $data[3] ;

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
      \WP_CLI::success( 'Import finished with ' . $updated_carrier_products . ' Carrier > Products updated.' );
    } else {
      \WP_CLI::error('I\'m unable to open your file.');
    }

  }
}
$nccCli = new NCC_Cli();
if( class_exists( '\\WP_CLI' ) )
  \WP_CLI::add_command( 'ncc', $nccCli );
