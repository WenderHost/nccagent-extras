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
  $options[] = '<option class="first-option" value="">Select a State...</option>';
  foreach( $terms as $term ){
    $options[] = '<option value="' . strtoupper( $term->slug ) . '-' . $term->term_id . '">' . $term->name . '</option>';
    $data[ strtoupper($term->slug) ] = $term->term_id;
  }
  $options = '<select class="dt-select" id="states" data-colId="1">' . implode( '', $options ) . '</select>';

  $state_options = [ 'data' => $data, 'options' => $options ];
  return $state_options;
}

/**
 * Returns an HTML template from `lib/html/`
 *
 * @param      string  $template  The template's name
 *
 * @return     string  The template
 */
function ncc_get_template( $template = null ){
  if( is_null( $template ) )
    return ncc_get_alert(['title' => 'No Template Requested', 'description' => 'Please specify a template.']);

  if( substr( $template, -5 ) != '.html' )
    $template.= '.html';

  $filename = plugin_dir_path( __FILE__ ) . '../html/' . $template;
  if( ! file_exists( $filename ) )
    return ncc_get_alert(['title' => 'Template not found!', 'description' => 'I could not find your template (<code>' . basename( $template ) . '</code>).']);

  $template = file_get_contents( $filename );

  $search = [
    '{{image_path}}',
    '{{kit_request_url}}',
    '{{site_url}}'
  ];
  $replace = [
    plugin_dir_url( __FILE__ ) . '../img/',
    site_url('contracting/kit-request/'),
    site_url(),
  ];
  $template = str_replace( $search, $replace, $template );
  return $template;
}