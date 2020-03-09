<?php
/**
 * NCC Carrier import/export.
 */
class NCC_Carriers_CLI  extends WP_CLI_Command{
  public function __construct(){
    $this->data_keys = [];
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
   * - Lower_Issue_Age
   * - Upper_Issue_Age
   * - Review_Date
   * - Source_File_Name
   * - Source_File_Date
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

          $product_columns = [
            'Row_ID'                  => $row_id,
            'Product'                 => $product_name,
            'Alternate_Product_Name'  => $product_details['alternate_product_name'],
            'Lower_Issue_Age'         => $product_details['lower_issue_age'],
            'Upper_Issue_Age'         => $product_details['upper_issue_age'],
            'Review_Date'             => $product_details['review_date'],
            'Source_File_Name'        => $product_details['source_file_name'],
            'Source_File_Date'        => $product_details['source_file_date'],
            'States'                  => $states,
          ];
          $items[] = array_merge( $carrier_columns, $product_columns );
        endwhile;
      } else {
        $items[] = array_merge( $carrier_columns, ['Row_ID' => '', 'Product' => '', 'Alternate_Product_Name' => '', 'Lower_Issue_Age' => '' , 'Upper_Issue_Age' => '', 'Review_Date' => '', 'Source_File_Name' => '', 'Source_File_Date' => '', 'States' => '' ] );
      }
    }
    $headers = ['ID','Carrier', 'Row_ID','Product','Alternate_Product_Name','Lower_Issue_Age','Upper_Issue_Age','Review_Date','Source_File_Name','Source_File_Date','States'];

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
            \WP_CLI::error('Your CSV needs the following header rows:' . "\n" . 'ID,Carrier,Row_ID,Product,Alternate_Product_Name,Lower_Issue_Age,Upper_Issue_Age,Review_Date,Source_File_Name,Source_File_Date,States');

          $headers = $data;
          // Get the keys for each column
          //$data_keys = [];
          foreach( $headers as $key => $column ){
            $column_name = $this->_format_column_name( $column );
            $this->data_keys[$column_name] = $key;
          }
          \WP_CLI::line('🔔 We are importing with the following columns: ' . implode( ', ', $headers ) );
          //\WP_CLI::error('Halting...$headers = ' . print_r( $headers, true ) . "\n" . '$this->data_keys = ' . print_r( $this->data_keys, true ) );
        } else {
          // Process the row
          $mapped_data = $this->_map_row_values( $data );
          //\WP_CLI::error('Here is our first row: $mapped_data = ' . print_r( $mapped_data, true ) );

          $states = $mapped_data['states'];
          $post_id = $mapped_data['id'];
          $product_name = ( ! empty( $mapped_data['alternate_product_name'] ) )? $mapped_data['alternate_product_name'] . ' (' . $mapped_data['product'] . ')' : $mapped_data['product'] ;

          // If no Row_ID, create a new row.
          if( empty( $mapped_data['row_id'] ) ){
            $row_id = $this->_create_carrier_product( $mapped_data );
            if( ! is_wp_error( $row_id ) ){
              $row_messages[] = '🔔 Created product for ' . $mapped_data['carrier'] . ' > ' . $product_name ;
              $items[] = [ 'Carrier' => $mapped_data['carrier'], 'Product' => $mapped_data['product'], 'Alt Product Name' => $mapped_data['alternate_product_name'], 'Lower Issue Age' => $mapped_data['lower_issue_age'], 'Upper Issue Age' => $mapped_data['upper_issue_age'], 'Review Date' => $mapped_data['review_date'], 'Source File Name' => $mapped_data['source_file_name'], 'Source File Date' => $mapped_data['source_file_date'], '# States' => count($states), '✅' => '💡' ];
              $new_carrier_products++;
            } else {
              $row_messages[] = '🚨 ' . $row_id->get_error_message();
            }
            continue; // By pass the below, no need to "update" since we just created all the meta fields.
          }

          $row_id = $mapped_data['row_id'] - 1;
          $row_updated = false;
          $selectors = ['states','review_date','source_file_name','source_file_date','lower_issue_age','upper_issue_age'];
          foreach( $selectors as $selector ){
            $field_name = 'products_' . $row_id . '_product_details_' . $selector;
            $field_updated = update_field( $field_name, $mapped_data[$selector], $mapped_data['id'] );
            if( $field_updated )
              $row_updated = true;
          }

          if( $row_updated ){
            $items[] = [ 'Carrier' => $mapped_data['carrier'], 'Product' => $mapped_data['product'], 'Alt Product Name' => $mapped_data['alternate_product_name'], 'Lower Issue Age' => $mapped_data['lower_issue_age'], 'Upper Issue Age' => $mapped_data['upper_issue_age'], 'Review Date' => $mapped_data['review_date'], 'Source File Name' => $mapped_data['source_file_name'], 'Source File Date' => $mapped_data['source_file_date'], '# States' => count($states), '✅' => '✅' ];
            $updated_carrier_products++;
          } else {
            $items[] = [ 'Carrier' => $mapped_data['carrier'], 'Product' => $mapped_data['product'], 'Alt Product Name' => $mapped_data['alternate_product_name'], 'Lower Issue Age' => $mapped_data['lower_issue_age'], 'Upper Issue Age' => $mapped_data['upper_issue_age'], 'Review Date' => $mapped_data['review_date'], 'Source File Name' => $mapped_data['source_file_name'], 'Source File Date' => $mapped_data['source_file_date'], '# States' => count($states), '✅' => '⛔︎' ];
          }
        }
        $progress->tick();
        $x++;
      }
      fclose( $h );
      $progress->finish();
      \WP_CLI\Utils\format_items( 'table', $items, ['Carrier','Product','Alt Product Name', 'Lower Issue Age', 'Upper Issue Age','Review Date','Source File Name','Source File Date','# States','✅'] );
      \WP_CLI::line( "| Key: ✅ Row updated • ⛔︎ Row not updated • 💡 Row created |\n+" . str_repeat('-', 59 ) . "+\n" );
      \WP_CLI::success( 'Import finished with ' . $updated_carrier_products . ' Carrier > Product(s) updated.' );
      if( 0 < $new_carrier_products )
        \WP_CLI::success( $new_carrier_products . ' Carrier > Product(s) created.' );

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
   * @param      array  $mapped_data   The data in an associative array
   *
   * @return     int\obj               Returns the Row_ID on success or a WP_Error object on fail.
   */
  private function _create_carrier_product( $mapped_data = array() ){
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
        'review_date'             => $mapped_data['review_date'],
        'source_file_name'        => $mapped_data['source_file_name'],
        'source_file_date'        => $mapped_data['source_file_date'],
        'description'             => '',
        'states'                  => $mapped_data['states'],
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

  /**
   * Formats a column heading.
   *
   * Formats a column heading according to these specs:
   *
   * - Trims whitespace before and after
   * - All lower case
   * - Replaces interior spaces with underscores
   *
   * Example: `Source File Name` becomes `source_file_name`.
   *
   * @param      string  $name   The column name
   *
   * @return     string  The formatted column name
   */
  private function _format_column_name( $name = '' ){
    if( empty( $name ) )
      \WP_CLI::error( '🚨 _format_column_name() received an empty input. Halting...' );

    $formatted_name = trim( $name );
    $formatted_name = strtolower( $formatted_name );
    $formatted_name = str_replace(' ', '_', $formatted_name );
    return $formatted_name;
  }

  /**
   * Maps a row of data to an associative array.
   *
   * Maps a row of CSV data to an associative array with the
   * array keys corresponding to the column heading.
   *
   * @param      array          $data   The row data
   *
   * @return     array|boolean  The row data mapped to an associative array.
   */
  private function _map_row_values( $data = [] ){
    if( 0 == count( $data ) )
      return false;

    $row_values = [];
    $data_keys = $this->data_keys;
    foreach ( $data_keys as $name => $key ) {
      switch( $name ){
        case 'review_date':
        case 'source_file_date':
          $date = date_create( $data[$key] );
          $row_values[$name] = date_format( $date, 'm/d/Y' );
          break;

        case 'states':
          $row_values['states'] = $this->_states_to_array( $data[$key] );
          break;

        default:
          $row_values[$name] = $data[$key];
      }

    }

    return $row_values;
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
