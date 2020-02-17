<?php

namespace NCCAgent\userprofiles;

/**
 * Adds fields to user profiles.
 *
 * Fields added:
 * - NPN
 * - Marketer
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
