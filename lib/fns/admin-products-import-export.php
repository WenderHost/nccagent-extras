<?php

namespace NCCAgent\productsadmin;

/**
 * Adds rewrite rule for downloading Carrier > Product CSVs.
 */
function add_rewrite_rules(){
  add_rewrite_rule( 'download-carrier-products\/([0-9]{1,}|[\*]{1})\/?', 'index.php?carrier_id=$matches[1]', 'top' );
}
add_action( 'init', __NAMESPACE__ . '\\add_rewrite_rules' );

/**
 * Adds `carrier` rewrite tag used for downloading Carrier > Product CSVs.
 */
function add_rewrite_tags(){
  add_rewrite_tag( '%carrier_id%', '^[0-9]{1,}|^[\*]{1}' );
}
add_action( 'init', __NAMESPACE__ . '\\add_rewrite_tags' );

/**
 * Adds admin page for Carrier > Products download GUI.
 */
function admin_menu(){
  $carrier_import_page = add_submenu_page( 'edit.php?post_type=carrier', 'Import/Export', 'Import/Export', 'activate_plugins', 'import_export', __NAMESPACE__ . '\\import_export_view' );
  $product_import_page = add_submenu_page( 'edit.php?post_type=product', 'Import/Export', 'Import/Export', 'activate_plugins', 'import_export', __NAMESPACE__ . '\\import_export_view' );

  add_action('load-' . $carrier_import_page, __NAMESPACE__ . '\\load_admin_js' );
  add_action('load-' . $product_import_page, __NAMESPACE__ . '\\load_admin_js' );
}
add_action( 'admin_menu', __NAMESPACE__ . '\\admin_menu' );

/**
 * Callback for loading our JS only on specific pages.
 */
function load_admin_js(){
  add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_enqueue_scripts' );
}

/**
 * Enqueues scripts and styles for the Carrier > Products download GUI.
 */
function admin_enqueue_scripts(){
  wp_enqueue_script('papa-parse', NCC_PLUGIN_URL . 'bower_components/papaparse/papaparse.min.js', null, filemtime( NCC_PLUGIN_DIR . 'bower_components/papaparse/papaparse.min.js' ) );
  wp_enqueue_script( 'products-import-export', NCC_PLUGIN_URL . 'lib/js/products-import-export.js', ['jquery','papa-parse'], filemtime( NCC_PLUGIN_DIR . 'lib/js/products-import-export.js' ) );
  wp_localize_script( 'products-import-export', 'wpvars', [
    'restUrl'       => rest_url( 'nccagent/v1/productimport' ),
    'downloadUrl'   => site_url( '/download-carrier-products/' ),
    'permalinkUrl'  => admin_url( 'options-permalink.php' ),
    'nonce'         => wp_create_nonce( 'wp_rest' )],
  );
  wp_enqueue_script( 'jquery-file-download', NCC_PLUGIN_URL . 'bower_components/jquery-file-download/src/Scripts/jquery.fileDownload.js', ['jquery', 'jquery-ui-dialog', 'jquery-ui-progressbar'] );
  wp_enqueue_style( 'wp-jquery-ui-dialog' );
}

/**
 * Downloads a CSV of Carrier > Products
 */
function download_carrier_products(){
  if( ! current_user_can( 'publish_pages' ) )
    return;

  global $wp_query;

  if( ! isset( $wp_query->query_vars['carrier_id'] ) )
    return;

  // Get all Carriers
  $args = [
    'post_type'   => 'carrier',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby'     => 'title',
    'order'       => 'ASC',
  ];

  $carrier_id = get_query_var( 'carrier_id' );
  ncc_error_log('🔔 $carrier_id = ' . $carrier_id );
  if( $carrier_id && is_numeric( $carrier_id ) ){
    $carrier_post = get_post( $carrier_id );
    if( $carrier_post )
      $args['name'] = $carrier_post->post_name;
  }

  $carriers = get_posts($args);
  if( ! $carriers ){
    $error_message = ( isset( $args['name'] ) )? 'No Carrier found with the name `' . $args['name'] . '`' : 'No Carriers found.' ;
    ncc_error_log( $error_message );
  }

  $items = [];
  $counter = 0;
  $skipped_rows = 0;
  foreach( $carriers as $carrier ){
    $carrier_columns = [ 'ID' => $carrier->ID, 'Carrier' => $carrier->post_title ];
    if( \have_rows( 'products', $carrier->ID ) ){
      while( \have_rows( 'products', $carrier->ID ) ): the_row();
        $row_id = get_row_index();
        $product = get_sub_field( 'product' );
        if( 'publish' != $product->post_status ){
          $skipped_rows++;
          continue;
        }
        $product_details = get_sub_field( 'product_details' );

        $states = ( ! empty($product_details['states']) )? implode(',', $product_details['states'] ) : '';
        $product_name = ( is_object( $product ) )? $product->post_title : '***NO_PRODUCT_FOUND***';

        $product_columns = [
          'Row_ID'                  => $row_id,
          'Product'                 => $product_name,
          'Alternate_Product_Name'  => $product_details['alternate_product_name'],
          'Alt_Product_Name_2'      => '',
          'Lower_Issue_Age'         => $product_details['lower_issue_age'],
          'Upper_Issue_Age'         => $product_details['upper_issue_age'],
          'Source_File_Name'        => $product_details['source_file_name'],
          'Source_File_Date'        => $product_details['source_file_date'],
          'Desc_Review_Date'        => $product_details['desc_review_date'],
          'States'                  => $states,
          'States_Review_Date'      => $product_details['states_review_date'],
          'Plan_Year'               => $product_details['plan_year'],
        ];
        $items[] = array_merge( $carrier_columns, $product_columns );
      endwhile;
    } else {
      $items[] = array_merge( $carrier_columns, ['Row_ID' => '', 'Product' => '', 'Alternate_Product_Name' => '', 'Alt_Product_Name_2' => '', 'Lower_Issue_Age' => '' , 'Upper_Issue_Age' => '', 'Source_File_Name' => '', 'Source_File_Date' => '', 'Desc_Review_Date' => '', 'States' => '', 'States_Review_Date' => '', 'Plan_Year' => '' ] );
    }
  }
  $headers = ['ID','Carrier', 'Row_ID','Product','Alternate_Product_Name','Alt_Product_Name_2','Lower_Issue_Age','Upper_Issue_Age','Source_File_Name','Source_File_Date','Desc_Review_Date','States','States_Review_Date', 'Plan_Year'];

  ncc_error_log('🔔🔔 $items = ' . print_r( $items, true ) );

  $csv = '"' . implode( '","', $headers ). '"';
  foreach( $items as $item ){
    $csv.= "\n" . '"' . implode( '","', $item ) . '"';
  }

  $filename = 'carrier-products_';
  if( array_key_exists( 'name', $args ) && ! empty( $args['name'] ) )
    $filename.= $args['name'] . '_';
  $filename.= current_time( 'Y-m-d_His' ) . '.csv';

  header('Set-Cookie: fileDownload=true; path=/');
  header('Cache-Control: max-age=60, must-revalidate');
  header("Content-type: text/csv");
  header('Content-Disposition: attachment; filename="' . $filename );
  echo $csv;
  die();
}
add_action( 'template_redirect', __NAMESPACE__ . '\\download_carrier_products' );


/**
 * Admin interface for downloading Carrier > Products
 */
function import_export_view(){
  ?>
  <div class="wrap">
    <h2>Carrier &gt; Products Import/Export</h2>
    <div class="wrap">
      <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
          <div id="post-body-content">
            <div class="meta-box-sortables ui-sortable">

                  <form action="javascript:void(0);" id="the_form">
                    <p>Use the file dialog below to select a CSV on your computer and upload it for import:</p>
                    <input type="file" id="the_file" accept=".csv">
                    <input type="submit" value="Import" class="button" />

                  </form>
                  <div id="file_info"></div>

                  <div class="notice notice-warning" id="uploadstatus" style="display: none;">
                    <p><strong>Importing</strong> <span id="uploadrow">0</span> of <span id="uploadtotal">0</span>. (<em>IMPORTANT: Do not close/reload this window until complete!</em>)</p>
                  </div>

                  <div id="list"></div>

            </div><!-- .meta-box-sortables -->
          </div><!-- #post-body-content -->
          <div id="postbox-container-1" class="postbox-container">
            <div class="meta-box-sortables">
              <div class="postbox">
                <h3>Export Carrier &gt; Products</h3>
                <div class="inside">
                  <p>Download Carrier &gt; Products as a CSV.</p>
                  <p><select name="carriers" id="carriers">
                    <?php
                    echo '<option value="*">All Carriers</option>';
                    $carriers = get_posts([
                      'post_type'   => 'carrier',
                      'numberposts' => -1,
                      'orderby'     => 'title',
                      'order'       => 'ASC',
                    ]);
                    if( ! $carriers ){
                      echo '<option select="selected">NO CARRIERS FOUND!</option>';
                    } else {
                      $option_format = '<option value="%1$s" %3$s>%2$s</option>';
                      foreach( $carriers as $carrier ){
                        echo sprintf( $option_format, $carrier->ID, esc_attr( $carrier->post_title ), '' );
                      }
                    }
                    ?>
                  </select></p>
                  <?php submit_button( 'Download Carrier &gt; Products', 'secondary', 'download-carriers', false  ) ?>
                </div>
              </div><!-- .postbox -->
            </div><!-- .meta-box-sortables -->
          </div><!-- .postbox-container -->
        </div><!-- #post-body -->
      </div><!-- #poststuff -->
    </div>
  </div>
  <?php
}