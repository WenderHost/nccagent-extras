<?php

/**
 * Returns an HTML alert message
 *
 * @param      array  $atts {
 *   @type  string  $type         The alert type (defaults to `warning`).
 *   @type  string  $title        The title of the alert.
 *   @type  string  $description  The content of the alert.
 * }
 *
 * @return     html  The alert.
 */
function ncc_get_alert( $atts ){
  $args = shortcode_atts([
   'type'         => 'warning',
   'title'        => 'Alert Title Goes Here',
   'description'  => 'Alert description goes here.',
  ], $atts );

  $search = ['{type}', '{title}', '{description}' ];
  $replace = [ esc_attr( $args['type'] ), $args['title'], $args['description'] ];
  $html = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/alert.html' );
  return str_replace( $search, $replace, $html );
}

/**
 * Builds a <select/> of `State` options from the States Taxonomy.
 *
 * @return     string  The state options.
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
    site_url('contracting/kit-request/'),
    site_url(),
  ];
  if( ! is_null( $args['replace'] ) && is_array( $args['replace'] ) && 0 < count( $args['replace'] ) )
    $replace = array_merge( $replace, $args['replace'] );

  $template = str_replace( $search, $replace, $template );
  return $template;
}
