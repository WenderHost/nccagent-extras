<?php

namespace NCCAgent\userprofiles;

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

function save_extra_user_profile_fields( $user_id ) {
  if ( !current_user_can( 'edit_user', $user_id ) ) {
      return false;
  }
  update_user_meta( $user_id, 'npn', $_POST['npn'] );
}
add_action( 'personal_options_update', __NAMESPACE__ . '\\save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', __NAMESPACE__ . '\\save_extra_user_profile_fields' );
