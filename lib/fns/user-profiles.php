<?php

namespace NCCAgent\userprofiles;

/**
 * Sends a message to approved users.
 *
 * @param      int  $user_id  The user identifier
 */
function approve_user_message($user_id){
  if ( get_user_meta( $user_id, 'wp-approve-user-new-registration', true ) ) {
    wp_new_user_notification( $user_id, null, 'user' );
    delete_user_meta( $user_id, 'wp-approve-user-new-registration' );
  }

  // Check user meta if mail has been sent already.
  if ( ! get_user_meta( $user_id, 'wp-approve-user-mail-sent', true ) ) {
    $user     = new \WP_User( $user_id );
    $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

  // Get the "Approve User Message Subject" from our ACF Options Page
  $approve_user_message_subject = get_field( 'approve_user_message_subject', 'option' );
  if( ! $approve_user_message_subject || empty( $approve_user_message_subject ) )
    $approve_user_message_subject = 'Your Account Has Been Approved';

  // Get the "Approve User Message" from our ACF Options Page
  $approve_user_message = get_field( 'approve_user_message', 'option' );
  if( ! $approve_user_message || empty( $approve_user_message ) )
    $approve_user_message = nl2br( "Your account at {site_name} has been approved.\n\nGet started by setting your password here: {home_url}/login\n\nBest Regards,\nThe NCC Team");

  // Replace any tokens in the message
  $search = ['{site_name}','{home_url}'];
  $replace = [ get_bloginfo( 'name' ), home_url() ];
  $approve_user_message = str_replace( $search, $replace, $approve_user_message );

  $from_address = get_field( 'from_address', 'option' );
  if( empty( $from_address ) || ! is_email( $from_address ) )
    $from_address = get_bloginfo( 'admin_email' );  

    // Send mail.
  $headers[] = 'From: ' . get_bloginfo("name") . ' <' . $from_address . '>' . "\r\n";
  $headers[] = 'Content-Type: text/html; charset=UTF-8';
  $sent = wp_mail(
    $user->user_email,
    $approve_user_message_subject,
    $approve_user_message,
    $headers
  );

    if ( $sent ) {
      update_user_meta( $user_id, 'wp-approve-user-mail-sent', true );
    }
  }
};
add_action( 'wpau_approve', __NAMESPACE__ . '\\approve_user_message' );

/**
 * Message sent to users after they submit the "Register" form.
 *
 * @param      int   $user_id  The user identifier
 *
 * @return     boolean  Returns FALSE if no user found by the provided ID.
 */
function create_user_message( $user_id ){
  $user = get_userdata( $user_id );

  if( ! $user )
    return false;

  // Get the "Create User Message Subject" from our ACF Options Page
  $create_user_message_subject = get_field( 'create_user_message_subject', 'option' );
  if( ! $create_user_message_subject || empty( $create_user_message_subject ) )
    $create_user_message_subject = 'Thank You for Registering with ' . get_bloginfo( 'name' );

  // Get the "Create User Message" from our ACF Options Page
  $create_user_message = get_field( 'create_user_message', 'option' );
  if( ! $create_user_message || empty( $create_user_message ) )
    $create_user_message = nl2br( "Dear {firstname},\n\nThank you for registering with {site_name}. We will review the details you provided and notify you once your account has been approved/disapproved.\n\nBest Regards,\nThe NCC Team" );

  // Replace any tokens in the message
  $search = ['{site_name}','{firstname}'];
  $replace = [ get_bloginfo( 'name' ), $user->first_name ];
  $create_user_message = str_replace( $search, $replace, $create_user_message );

  $from_address = get_field( 'from_address', 'option' );
  if( empty( $from_address ) || ! is_email( $from_address ) )
    $from_address = get_bloginfo( 'admin_email' );

  $headers[] = 'From: ' . get_bloginfo("name") . ' <' . $from_address . '>' . "\r\n";
  $headers[] = 'Content-Type: text/html; charset=UTF-8';
  wp_mail($user->user_email, $create_user_message_subject, $create_user_message, $headers);
}

/**
 * Sends the user an email when they are deleted.
 *
 * @param      string  $user_id  The user ID
 */
function delete_user_message( $user_id ){
  $disable_delete_user_message = get_field('disable_delete_user_message', 'option' );
  if( is_array( $disable_delete_user_message ) && ( 0 < count( $disable_delete_user_message ) ) && $disable_delete_user_message[0]['value'] )
    return;

  global $wpdb;
  $email = $wpdb->get_var("SELECT user_email FROM $wpdb->users WHERE ID = '" . $user_id . "' LIMIT 1");

  // Get the "Delete User Message Subject" from our ACF Options Page
  $delete_user_message_subject = get_field( 'delete_user_message_subject', 'option' );
  if( ! $delete_user_message_subject || empty( $delete_user_message_subject ) )
    $delete_user_message_subject = 'Account Not Approved';

  // Get the "Delete User Message" from our ACF Options Page
  $delete_user_message = get_field( 'delete_user_message', 'option' );
  if( ! $delete_user_message || empty( $delete_user_message ) )
    $delete_user_message = nl2br("Your account at {site_name} was not approved. If you feel this decision was an error, please contact us to appeal.\n\nBest Regards,\nThe NCC Team");

  // Replace any tokens in the message
  $search = ['{site_name}'];
  $replace = [ get_bloginfo( 'name' ) ];
  $delete_user_message = str_replace( $search, $replace, $delete_user_message );

  $from_address = get_field( 'from_address', 'option' );
  if( empty( $from_address ) || ! is_email( $from_address ) )
    $from_address = get_bloginfo( 'admin_email' );  

  $headers[] = 'From: ' . get_bloginfo("name") . ' <' . $from_address . '>' . "\r\n";
  $headers[] = 'Content-Type: text/html; charset=UTF-8';
  wp_mail($email, $delete_user_message_subject, $delete_user_message, $headers);
}
add_action( 'delete_user', __NAMESPACE__ . '\\delete_user_message' );

/**
 * Adds extra columns to the Users' table in the WP Admin.
 *
 * @param      array  $columns  The columns
 *
 * @return     array  The filtered array of columns.
 */
function extra_user_table_columns( $columns ) {
    $columns['marketer_id'] = 'Marketer';
    return $columns;
}
add_filter( 'manage_users_columns', __NAMESPACE__ . '\\extra_user_table_columns' );

/**
 * Adds content to our additional WP User Table columns.
 *
 * @param      string  $val          The value
 * @param      string  $column_name  The column name
 * @param      int     $user_id      The user identifier
 *
 * @return     string  Our column content.
 */
function marketer_column_content( $val, $column_name, $user_id ){
  switch ($column_name) {
    case 'marketer_id':
      $marketer_id = get_the_author_meta( 'marketer_id', $user_id );
      $marketer_name = get_the_title( $marketer_id );
      $val = $marketer_name;
      break;
  }
  return $val;
}
add_filter( 'manage_users_custom_column', __NAMESPACE__ . '\\marketer_column_content', 10, 3 );

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
    <tr>
        <th><label for="company"><?php _e("Company"); ?></label></th>
        <td>
            <input type="text" name="company" id="company" value="<?php echo esc_attr( get_the_author_meta( 'company', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Please enter your Company."); ?></span>
        </td>
    </tr>
    <?php
    $current_marketer = get_the_author_meta( 'marketer_id', $user->ID );
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
    $marketer_select = '<select name="marketer_id" id="marketer_id">' . implode( '', $options ) . '</select>';
    ?>
    <tr>
        <th><label for="marketer_id"><?php _e("Marketer"); ?></label></th>
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
  update_user_meta( $user_id, 'company', $_POST['company'] );
  update_user_meta( $user_id, 'marketer_id', $_POST['marketer_id'] );
}
add_action( 'personal_options_update', __NAMESPACE__ . '\\save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', __NAMESPACE__ . '\\save_extra_user_profile_fields' );
