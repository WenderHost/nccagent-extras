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
          $product = get_sub_field( 'product' );
          $product_details = get_sub_field( 'product_details' );
          $states = ( ! empty($product_details['states']) )? implode(',', $product_details['states'] ) : '';
          $product_name = ( is_object( $product ) )? $product->post_title : '***NO_PRODUCT_FOUND***';
          $product_columns = [ 'Product' => $product_name, 'Alternate_Product_Name' => $product_details['alternate_product_name'], 'States' => $states ];
          $items[] = array_merge( $carrier_columns, $product_columns );
        endwhile;
      } else {
        $items[] = array_merge( $carrier_columns, ['Product' => '', 'Alternate_Product_Name' => '', 'States' => '' ] );
      }
    }
    $headers = ['ID','Carrier','Product','Alternate_Product_Name','States'];

    // Display the data
    \WP_CLI\Utils\format_items( 'table', $items, $headers );

    // Save the data as a CSV
    $csv_filename = current_time( 'Y-m-d_Gis') . '_carriers-and-products.csv';
    $handle = fopen( $csv_filename, 'w' );
    \WP_CLI\Utils\write_csv( $handle, $items, $headers );
    fclose( $handle );
    \WP_CLI::success('Created ' . $csv_filename );
  }
}
$nccCli = new NCC_Cli();
if( class_exists( '\\WP_CLI' ) )
  \WP_CLI::add_command( 'ncc', $nccCli );
