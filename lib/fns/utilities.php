<?php

/**
 * Formats an array of state names as HTML chiclets.
 *
 * @param      array|string  $states  The states
 *
 * @return     string  HTML for State chiclets.
 */
function ncc_build_state_chiclets( $states = array() ){
  if( is_array( $states ) )
    sort( $states );

  if( is_array( $states ) ){
    $state_html = '';
    foreach( $states as $state ){
      $state_html.= '<span class="chiclet chiclet-' . strtolower( $state ) . '">' . $state . '</span> ';
    }
    $states = $state_html;
  }

  return $states;
}

function ncc_bust_cache(){
  $css = file_get_contents( NCC_PLUGIN_DIR . 'lib/css/cache-busters.css' );
  echo '<style type="text/css">' . $css . '</style>';
}
add_action( 'wp_footer', 'ncc_bust_cache' );

/**
 * Returns an HTML alert message
 *
 * @param      array  $atts {
 *   @type  string  $type         The alert type can info, warning, success, or danger (defaults to `warning`).
 *   @type  string  $title        The title of the alert.
 *   @type  string  $description  The content of the alert.
 *   @type  string  $css_classes  Additional CSS classes to add to the alert parent <div>.
 * }
 *
 * @return     html  The alert.
 */
function ncc_get_alert( $atts ){
  $args = shortcode_atts([
   'type'               => 'warning',
   'title'              => 'Alert Title Goes Here',
   'description'        => 'Alert description goes here.',
   'css_classes' => null,
  ], $atts );

  $title = ( ! empty( $args['title'] ) )? '<span class="elementor-alert-title">' . $args['title'] . '</span>' : '' ;

  $search = ['{type}', '{title}', '{description}', '{css_classes}' ];
  $replace = [ esc_attr( $args['type'] ), $title, $args['description'], $args['css_classes'] ];
  $html = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/alert.html' );
  return str_replace( $search, $replace, $html );
}

/**
 * Builds a <select/> of `State` options from the States Taxonomy.
 *
 * Returns an array with the following key => value pairs:
 *
 * @type  array   data     Key => value pairs of `slug` => `term_id` (e.g. AL => 33).
 * @type  string  options  HTML <select> with `slug-term_id` option values (e.g. <option value="AL-33">Alabama</option>).
 * @type  array   library  `slug` => `name` pairs (e.g. AL => Alabama).
 *
 * @return     array  State options as a `data` array, HTML select, and `library` for easy lookup.
 */
function ncc_get_state_options(){
  $terms = get_terms([
    'taxonomy'    => 'state',
    'hide_empty'  => false,
    'orderby'     => 'name',
    'order'       => 'ASC',
  ]);
  $options = [];
  $library = [];
  $options[] = '<option class="first-option" value="">Select a State...</option>';
  foreach( $terms as $term ){
    $options[] = '<option value="' . strtoupper( $term->slug ) . '-' . $term->term_id . '">' . $term->name . '</option>';
    $library[strtoupper($term->slug)] = $term->name;
    $data[ strtoupper($term->slug) ] = $term->term_id;
  }
  $options = '<select class="dt-select" id="states" data-colId="1">' . implode( '', $options ) . '</select>';

  $state_options = [ 'data' => $data, 'options' => $options, 'library' => $library ];
  return $state_options;
}

/**
 * Given a state abbreviation, returns the full name of the state.
 *
 * @param      string      $abbr   The abbr
 *
 * @return     string|bool  Returns the state name. FALSE on failure.
 */
function ncc_get_state_name( $abbr = null ){
  if( is_null( $abbr) )
    return false;

  $state_names = [ 'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MI' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming' ];
  if( array_key_exists( strtoupper( $abbr ), $state_names ) )
    return $state_names[$abbr];

  return false;
}

/**
 * Returns an HTML template from `lib/html/`
 *
 * @param      array  $atts {
 *   @type   string  $template The template
 *   @type   array   $search   An array of items we are searching to replace.
 *   @type   array   $replace  An array of replacements
 * }
 *
 * @return     string  The template
 */
function ncc_get_template( $atts ){

  // If we call this function w/o passing an array
  // of attributes, assume we've passed a string
  // for the template:
  if( ! is_array( $atts ) )
    $atts = [ 'template' => $atts ];

  $args = shortcode_atts([
    'template'  => null,
    'search'    => null,
    'replace'   => null,
  ], $atts );

  if( is_null( $args['template'] ) )
    return ncc_get_alert(['title' => 'No Template Requested', 'description' => 'Please specify a template.']);

  if( substr( $args['template'], -5 ) != '.html' )
    $args['template'].= '.html';

  $filename = plugin_dir_path( __FILE__ ) . '../html/' . $args['template'];
  if( ! file_exists( $filename ) )
    return ncc_get_alert(['title' => 'Template not found!', 'description' => 'I could not find your template (<code>' . basename( $template ) . '</code>).']);

  if( NCC_DEV_ENV ){
    $template = file_get_contents( $filename );
  } else {
    $template_transient_key = 'ncc_get_template/' . $args['template'];
    if( false === ( $template = get_transient( $template_transient_key ) ) ){
      $template = file_get_contents( $filename );
      set_transient( $template_transient_key, $template, HOUR_IN_SECONDS );
    }
  }

  $search = [
    '{{image_path}}',
    '{{kit_request_url}}',
    '{{site_url}}'
  ];
  if( ! is_null( $args['search'] ) && is_array( $args['search'] ) && 0 < count( $args['search'] ) )
    $search = array_merge( $search, $args['search'] );

  $replace = [
    plugin_dir_url( __FILE__ ) . '../img/',
    home_url('contracting/kit-request/'),
    home_url(),
  ];
  if( ! is_null( $args['replace'] ) && is_array( $args['replace'] ) && 0 < count( $args['replace'] ) )
    $replace = array_merge( $replace, $args['replace'] );

  $template = str_replace( $search, $replace, $template );
  return $template;
}

/**
 * Returns TRUE if we are editing inside Elementor.
 *
 * @return     bool  TRUE if editing in Elementor.
 */
function ncc_is_elementor_edit_mode(){
  return \Elementor\Plugin::$instance->editor->is_edit_mode();
}

/**
 * Given a product title, returns `true` if it matches
 * any of the medicare related strings we check against.
 *
 * @param      string   $product_title  The product title
 *
 * @return     boolean  True if a medicare product.
 */
function ncc_is_medicare_product( $product_title = '' ){
  $medicare_product = false;
  $medicare_products = ['Medicare','Prescription Drug Plan','PDP'];
  foreach ( $medicare_products as $value ) {
    if( stristr( $product_title, $value ) )
      $medicare_product = true;
  }
  return $medicare_product;
}
