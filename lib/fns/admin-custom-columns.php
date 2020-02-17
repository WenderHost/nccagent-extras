<?php

namespace NCCAgent\customcolumns;

/**
 * Adds a `States` column to the Team Member CPT
 *
 * @param      array  $columns  The columns
 *
 * @return     array  Filtered $columns array
 */
function set_team_member_edit_columns($columns) {
  $columns['photo']       = __( 'Photo', 'nccagent' );
  $columns['states']      = __( 'States', 'nccagent' );
  $columns['email']       = __( 'Email', 'nccagent' );
  $columns['staff_type']  = __( 'Staff Type(s)', 'nccagent' );
  $columns['title']       = __( 'Name', 'nccagent' );

  // Re-order columns
  $columns = [
    'cb' => $columns['cb'],
    'photo' => $columns['photo'],
    'title' => $columns['title'],
    'states' => $columns['states'],
    'email' => $columns['email'],
    'staff_type' => $columns['staff_type'],
  ];
  return $columns;
}
add_filter( 'manage_team_member_posts_columns', __NAMESPACE__ . '\\set_team_member_edit_columns' );

/**
 * Populates the `State` column for the Team Member CPT admin listing.
 *
 * @param      string  $column   The column
 * @param      int     $post_id  The post identifier
 */
function custom_team_member_column( $column, $post_id ){
  switch( $column ){
    case 'email':
      $email = get_post_meta( $post_id, 'email', true );
      echo $email;
      break;

    case 'photo':
      if( has_post_thumbnail( $post_id ) )
        the_post_thumbnail('thumbnail', ['style' => 'width: 48px; height: 48px;'] );
      break;

    case 'staff_type':
      $staff_types = get_the_term_list( $post_id, 'staff_type' );
      if( is_string( $staff_types ) )
        echo $staff_types;
      break;

    case 'states':
      $terms = get_the_term_list( $post_id, 'state', '', ', ', '' );
      if( is_string( $terms ) )
        echo $terms;
      break;
  }
}
add_action( 'manage_team_member_posts_custom_column', __NAMESPACE__ . '\\custom_team_member_column', 10, 2 );