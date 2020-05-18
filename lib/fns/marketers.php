<?php

namespace NCCAgent\marketer;

/**
 * Displays the contact details for a marketer
 *
 * @param      array  $atts {
 *   @type  int  $id The Marketer CPT post ID.
 * }
 *
 * @return     string  HTML for the Marketer's contact details.
 */
function marketer_contact_details( $atts ){
  global $post;

  $args = shortcode_atts( [
    'id' => null,
  ], $atts );

  $marketer_id = ( ! is_null( $args['id'] ) && is_numeric( $args['id'] ) )? $args['id'] : $post->ID ;

  $name = explode(' ', get_the_title( $post ) );
  $lastname = array_pop( $name );

  $html = '';
  $marketerFields = get_fields( $marketer_id, false );
  $marketerFields['hubspot'] = get_field( 'hubspot', $marketer_id );
  $template = ncc_get_template([
    'template'  => 'marketer_contact_details',
    'search'    => ['{{first_name}}'],
    'replace'   => [ implode(' ', $name ) ],
  ]);
  $extension = ( ! empty( $marketerFields['extension'] ) )? ' ext. ' . $marketerFields['extension'] : '';
  $search = [ '{phone}', '{extension}', '{email}', '{calendar_link}' ];
  $replace = [ $marketerFields['phone'], $extension, $marketerFields['email'], $marketerFields['hubspot']['calendar_link'] ];
  $html = str_replace( $search, $replace, $template );

  return $html;
}
add_shortcode( 'marketer_contact_details', __NAMESPACE__ . '\\marketer_contact_details' );

/**
 * Shows a listing of a marketer's states served.
 *
 * @param      array  $atts {
 *   @type  int  $id The Marketer CPT ID.
 * }
 *
 * @return     string  HTML for displaying the Marketer's states served.
 */
function marketer_states( $atts ){
  global $post;

  $args = shortcode_atts( [
    'id' => null,
  ], $atts );

  $marketer_id = ( ! is_null( $args['id'] ) && is_numeric( $args['id'] ) )? $args['id'] : $post->ID ;

  $terms = wp_get_post_terms( $marketer_id, 'state' );
  if( ! $terms ){
    $alert = ncc_get_alert(['title' => 'No states assigned!', 'description' => 'No states have been assigned to this marketer. Please add some <a href="' . get_edit_post_link( $marketer_id ) . '">here</a>.']);
    $html = ( is_user_logged_in() && current_user_can( 'activate_plugins' ) )? $alert : '' ;
    return $html;
  } else {
    $states = [];
    foreach ( $terms as $key => $term ) {
      $states[] = strtoupper( $term->slug );
    }
    $state_chiclets = ncc_build_state_chiclets( $states );
  }

  $html = '<h3 style="margin-bottom: 10px;">States served</h3>';
  $html.= '<p>' . $state_chiclets . '</p>';
  return $html;
}
add_shortcode( 'marketer_states', __NAMESPACE__ . '\\marketer_states' );

/**
 * Displays a Marketer's testimonials.
 *
 * @param      array  $atts {
 *    @type  int  $id  Team Member CPT post ID.
 * }
 *
 * @return     string Team Member testimonials HTML.
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
    return null;
  //return '<p>Testimonials coming soon. If you have a testimonials to share about ' . $name[0] . ', please share it with us at NCC.</p>';

  $html = '<h3>Testimonials</h3>';
  $template = ncc_get_template('testimonial');
  while( have_rows('testimonials') ): the_row();
    $testimonial = get_sub_field('testimonial');
    $photo = ( $testimonial['photo'] )? '<div class="elementor-testimonial-image">' . wp_get_attachment_image( $testimonial['photo']['ID'], 'thumbnail', $icon = false, $attr = '' ) . '</div>' : '' ;
    $headline = ( $testimonial['headline'] )? '<h5 class="headline">' . $testimonial['headline'] . '</h5>' : '' ;
    $search = [ '{{headline}}', '{{text}}', '{{name}}', '{{description}}', '{{photo}}' ];
    $replace = [ $headline, $testimonial['text'], $testimonial['name'], $testimonial['description'], $photo ];
    $html.= str_replace( $search, $replace, $template );
  endwhile;

  return $html;
}
add_shortcode( 'marketer_testimonials', __NAMESPACE__ . '\\marketer_testimonials' );

/**
 * Displays a user's assigned Team Member (i.e. Marketer)
 *
 * @param      array  $atts {
 *  @type  int  $id  The post ID of the Team Member CPT.
 * }
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
  if( ! $marketer_id ){
    //return ncc_get_alert(['title' => 'No Team Member Assigned', 'description' => 'No Team Member has been assigned to your user profile. Please contact NCC to have our staff assign a Team Member to you.']);
    return null;
  }

  $marketer = get_post( $marketer_id );
  if( ! $marketer || 'publish' != $marketer->post_status )
    return ncc_get_alert(['title' => 'No Marketer Found', 'description' => 'No marketer was found for your user profile. Please contact NCC so that we can assign a marketer to your profile.']);

  $html = '';
  $photo = get_the_post_thumbnail_url( $marketer->ID, 'medium' );
  $marketerFields = get_fields( $marketer->ID, false );
  $marketerFields['hubspot'] = get_field( 'hubspot', $marketer->ID );
  $template = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/marketer.html' );

  $search = [ '{photo}', '{name}', '{title}', '{phone}', '{email}', '{marketer_page}', '{calendar_link}', '{extension}' ];

  $calendarLink = ( ! empty( $marketerFields['hubspot']['calendar_link'] ) )? $marketerFields['hubspot']['calendar_link'] : '';
  $extension = ( ! empty( $marketerFields['extension'] ) )? ' ext. ' . $marketerFields['extension'] : '' ;
  $replace = [ $photo, $marketer->post_title, $marketerFields['title'], $marketerFields['phone'], $marketerFields['email'], get_permalink( $marketer_id ), $calendarLink, $extension ];

  $html = str_replace( $search, $replace, $template );

  return $html;
}
add_shortcode( 'mymarketer', __NAMESPACE__ . '\\my_marketer' );