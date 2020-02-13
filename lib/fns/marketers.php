<?php

namespace NCCAgent\marketer;

function marketer_contact_details( $atts ){
  global $post;

  $args = shortcode_atts( [
    'id' => null,
  ], $atts );

  $marketer_id = ( ! is_null( $args['id'] ) && is_numeric( $args['id'] ) )? $args['id'] : $post->ID ;


  $html = '';
  $marketerFields = get_fields( $marketer_id, false );
  $template = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/marketer_contact_details.html' );
  $extension = ( ! empty( $marketerFields['extension'] ) )? ' ext. ' . $marketerFields['extension'] : '';
  $search = [ '{phone}', '{extension}', '{email}' ];
  $replace = [ $marketerFields['phone'], $extension, $marketerFields['email'] ];
  $html = str_replace( $search, $replace, $template );

  return $html;
}
add_shortcode( 'marketer_contact_details', __NAMESPACE__ . '\\marketer_contact_details' );

/**
 * Displays a Marketer's testimonials.
 *
 * @param      <type>  $atts   The atts
 */
function marketer_testimonials( $atts ){
  global $post;

  $args = shortcode_atts( [
    'id' => null,
  ], $atts );

  $marketer_id = ( ! is_null( $args['id'] ) && is_numeric( $args['id'] ) )? $args['id'] : $post->ID ;

  $marketer = get_post( $marketer_id );
  $name = explode( ' ', $marketer->post_title );

  if( ! have_rows('testimonials') )
    return '<p>Testimonials coming soon. If you have a testimonials to share about ' . $name[0] . ', please share it with us at NCC.</p>';

  $html = '<h3>Testimonials</h3>';
  $template = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/testimonial.html' );
  while( have_rows('testimonials') ): the_row();
    $testimonial = get_sub_field('testimonial');
    $search = [ '{text}', '{name}', '{description}' ];
    $replace = [ $testimonial['text'], $testimonial['name'], $testimonial['description'] ];
    $html.= str_replace( $search, $replace, $template );
  endwhile;

  return $html;
}
add_shortcode( 'marketer_testimonials', __NAMESPACE__ . '\\marketer_testimonials' );

/**
 * Displays a user's assigned Team Member (i.e. Marketer)
 *
 * @param      <type>  $atts   The atts
 *
 * @return     string  Marketer HTML.
 */
function my_marketer( $atts ){
  $args = shortcode_atts( [
    'id' => null,
  ], $atts );

  $user = wp_get_current_user();
  if( ! $user )
    return '<p><strong>No User Found!</strong> You don\'t appear to be logged in.</p>';

  $marketer_id = get_user_meta( $user->ID, 'marketer', true );
  if( ! $marketer_id )
    return '<p><strong>No Team Member Assigned</strong><br />No Team Member has been assigned to your user profile. Please contact NCC to have our staff assign a Team Member to you.</p>';

  $marketer = get_post( $marketer_id );

  $html = '';
  $photo = get_the_post_thumbnail_url( $marketer->ID, 'medium' );
  $marketerFields = get_fields( $marketer->ID, false );
  $template = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/marketer.html' );
  $search = [ '{photo}', '{name}', '{title}', '{phone}', '{email}' ];
  $replace = [ $photo, $marketer->post_title, $marketerFields['title'], $marketerFields['phone'], $marketerFields['email'] ];
  $html = str_replace( $search, $replace, $template );

  return $html;
}
add_shortcode( 'mymarketer', __NAMESPACE__ . '\\my_marketer' );