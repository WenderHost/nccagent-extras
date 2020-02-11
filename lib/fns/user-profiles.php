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
}
add_action( 'personal_options_update', __NAMESPACE__ . '\\save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', __NAMESPACE__ . '\\save_extra_user_profile_fields' );

function my_marketer( $atts ){
  $args = shortcode_atts( [
    'foo' => 'bar',
  ], $atts );

  $user = wp_get_current_user();
  if( ! $user )
    return '<p><strong>No User Found!</strong>You don\'t appear to be logged in.</p>';

  $marketers = get_posts([
    'post_type'     => 'team_member',
    'meta_query'    => [
      [
        'key'     => 'agents',
        'value'   => serialize( $user->ID ),
        'compare' => 'LIKE'
      ]
    ]
  ]);
  $html = '';
  foreach( $marketers as $marketer ){
    $photo = get_the_post_thumbnail_url( $marketer->ID, 'medium' );
    $marketerFields = get_fields( $marketer->ID, false );
    $template = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/marketer.html' );
    $search = [ '{photo}', '{name}', '{title}', '{phone}', '{email}' ];
    $replace = [ $photo, $marketer->post_title, $marketerFields['title'], $marketerFields['phone'], $marketerFields['email'] ];
    $html = str_replace( $search, $replace, $template );
    //$html.= '<div class="marketer user-profile"><class="row">' . $photo . '<h5>Your NCC Contact:</h5><h3>' . $marketer->post_title . '<span class="">' . $marketerFields['title'] . '</span></h3><p>' . $marketerFields['phone'] . ' &bull; <a href="' . $marketerFields['email'] . '">' . $marketerFields['email'] . '</a></p></div></div>';
  }

  return $html;
}
add_shortcode( 'mymarketer', __NAMESPACE__ . '\\my_marketer' );