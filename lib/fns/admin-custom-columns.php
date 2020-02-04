<?php

namespace NCCAgent\customcolumns;

// Add the custom columns to the book post type:

/**
 * Adds a `States` column to the Team Member CPT
 *
 * @param      array  $columns  The columns
 *
 * @return     array  Filtered $columns array
 */
function set_team_member_edit_columns($columns) {
    $columns['states'] = __( 'States', 'nccagent' );
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
    case 'states':
      $terms = get_the_term_list( $post_id, 'state', '', ', ', '' );
      if( is_string( $terms ) )
        echo $terms;
      break;
  }
}
add_action( 'manage_team_member_posts_custom_column', __NAMESPACE__ . '\\custom_team_member_column', 10, 2 );