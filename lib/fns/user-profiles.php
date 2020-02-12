<?php

namespace NCCAgent\userprofiles;

/**
 * Adds fields to user profiles
 *
 * @param      object  $user   The user
 */
function extra_user_profile_fields( $user ) {
?>
    <h3><?php _e("NCC profile information", "blank"); ?></h3>

    <table class="form-table">
    <tr>
        <th><label for="npn"><?php _e("NPN"); ?></label></th>
        <td>
            <input type="text" name="npn" id="npn" value="<?php echo esc_attr( get_the_author_meta( 'npn', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Please enter your NPN."); ?></span>
        </td>
    </tr>
    <?php
    $current_marketer = get_the_author_meta( 'marketer', $user->ID );
    $marketers = get_posts([
      'post_type'   => 'team_member',
      'orderby'     => 'title',
      'order'       => 'ASC',
      'numberposts' => -1,
      'tax_query'   => [
        [
          'taxonomy'  => 'staff_type',
          'field'     => 'slug',
          'terms'     => 'marketing',
        ]
      ],
    ]);
    $options[] = '<option value="">Select agents\'s Marketer...</option>';
    if( $marketers ){
      foreach( $marketers as $marketer ){
        //$selected = ( $marketer->ID == $current_marketer )? ' selected="selected"' : '';
        $options[] = '<option value="' . $marketer->ID . '"' . selected( $marketer->ID, $current_marketer, false ) . '>' . $marketer->post_title . '</option>';
      }
    } else {
      $options[] = '<option value="" selected="selected">No Marketers found.</option>';
    }
    $marketer_select = '<select name="marketer" id="marketer">' . implode( '', $options ) . '</select>';
    ?>
    <tr>
        <th><label for="marketer"><?php _e("Marketer"); ?></label></th>
        <td>
            <?php echo $marketer_select; ?>
            <br /><span class="description"><?php _e("Select this agent's marketer."); ?></span>
        </td>
    </tr>
    </table>
<?php
}
add_action( 'show_user_profile', __NAMESPACE__ . '\\extra_user_profile_fields' );
add_action( 'edit_user_profile', __NAMESPACE__ . '\\extra_user_profile_fields' );

/**
 * Saves data from extra user profile fields
 *
 * @param      int   $user_id  The user identifier
 *
 * @return     boolean  Returns `false` when user data isn't saved.
 */
function save_extra_user_profile_fields( $user_id ) {
  if ( !current_user_can( 'edit_user', $user_id ) ) {
      return false;
  }
  update_user_meta( $user_id, 'npn', $_POST['npn'] );
  update_user_meta( $user_id, 'marketer', $_POST['marketer'] );
}
add_action( 'personal_options_update', __NAMESPACE__ . '\\save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', __NAMESPACE__ . '\\save_extra_user_profile_fields' );

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
    return '<p><strong>No Team Member Assigned</strong> No Team Member has been assigned to your user profile. Please contact NCC to have our staff assign a Team Member to you.</p>';

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