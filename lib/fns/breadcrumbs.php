<?php

namespace NCCAgent\breadcrumbs;

/**
 * Breadcrumbs with dropdown menus for child pages
 *
 * @param      array  $atts   The atts
 */
function custom_breadcrumbs( $atts ) {
    $args = shortcode_atts([
        'separator'         => '&gt;',
        'breadcrumbs_id'    => 'breadcrumbs',
        'breadcrumbs_class' => 'breadcrumbs',
        'home_title'        => 'Home',
    ], $atts );

    // Settings
    $separator          = $args['separator'];
    $breadcrumbs_id     = $args['breadcrumbs_id'];
    $breadcrumbs_class  = $args['breadcrumbs_class'];
    $home_title         = $args['home_title'];
    $html               = [];

    $carrierproduct = sanitize_title_with_dashes( get_query_var( 'carrierproduct' ) );

    // If you have any custom post types with custom taxonomies, put the taxonomy name below (e.g. product_cat)
    $custom_taxonomy    = 'product_cat';

    // Get the query & post information
    global $post,$wp_query;

    // Do not display on the homepage
    if ( ! is_front_page() ) {

        // Build the breadcrums
        $html[] = '<ul id="' . $breadcrumbs_id . '" class="' . $breadcrumbs_class . '">';

        // Home page
        $html[] = '<li class="item-home"><a class="bread-link bread-home" href="' . get_home_url() . '" title="' . $home_title . '">' . $home_title . '</a></li>';
        $html[] = '<li class="separator separator-home"> ' . $separator . ' </li>';

        if ( is_archive() && !is_tax() && !is_category() && !is_tag() ) {

            $html[] = '<li class="item-current item-archive"><strong class="bread-current bread-archive">' . post_type_archive_title($prefix, false) . '</strong></li>';

        } else if ( is_archive() && is_tax() && !is_category() && !is_tag() ) {

            // If post is a custom post type
            $post_type = get_post_type();

            // If it is a custom post type display name and link
            if($post_type != 'post') {

                $post_type_object = get_post_type_object($post_type);
                $post_type_archive = get_post_type_archive_link($post_type);

                $html[] = '<li class="item-cat item-custom-post-type-' . $post_type . '"><a class="bread-cat bread-custom-post-type-' . $post_type . '" href="' . $post_type_archive . '" title="' . $post_type_object->labels->name . '">' . $post_type_object->labels->name . '</a></li>';
                $html[] = '<li class="separator"> ' . $separator . ' </li>';

            }

            $custom_tax_name = get_queried_object()->name;
            $html[] = '<li class="item-current item-archive"><strong class="bread-current bread-archive">' . $custom_tax_name . '</strong></li>';

        } else if ( is_single() ) {

          // If post is a custom post type
          $post_type = get_post_type();

          // If it is a custom post type display name and link
          if($post_type != 'post') {

            $post_type_object = get_post_type_object( $post_type );
            $post_type_archive = get_post_type_archive_link( $post_type );

            $link_text = ( 'Carriers' == $post_type_object->labels->name )? 'Carriers &amp; Products' : $post_type_object->labels->name ;

            $html[] = '<li class="item-cat item-custom-post-type-' . $post_type . '"><a class="bread-cat bread-custom-post-type-' . $post_type . '" href="' . $post_type_archive . '" title="' . $post_type_object->labels->name . '">' . $link_text . '</a></li>';
            $html[] = '<li class="separator"> ' . $separator . ' </li>';

          }

          // Get post category info
          $category = get_the_category();

          if( ! empty( $category ) ) {

            // Get last category post is in
            $last_category = end( array_values( $category ) );

            // Get parent any categories and create array
            $get_cat_parents = rtrim( get_category_parents( $last_category->term_id, true, ',' ), ',' );
            $cat_parents = explode(',',$get_cat_parents);

            // Loop through parent categories and store in variable $cat_display
            $cat_display = '';
            foreach($cat_parents as $parents) {
              $cat_display .= '<li class="item-cat">'.$parents.'</li>';
              $cat_display .= '<li class="separator"> ' . $separator . ' </li>';
            }

          }

          // If it's a custom post type within a custom taxonomy
          $taxonomy_exists = taxonomy_exists($custom_taxonomy);
          if(empty($last_category) && !empty($custom_taxonomy) && $taxonomy_exists) {

            $taxonomy_terms = get_the_terms( $post->ID, $custom_taxonomy );
            $cat_id         = $taxonomy_terms[0]->term_id;
            $cat_nicename   = $taxonomy_terms[0]->slug;
            $cat_link       = get_term_link($taxonomy_terms[0]->term_id, $custom_taxonomy);
            $cat_name       = $taxonomy_terms[0]->name;

          }

          // Check if the post is in a category
          if( ! empty( $last_category ) ) {
            $html[] = $cat_display;
            $html[] = '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</strong></li>';

          // Else if post is in a custom taxonomy
          } else if( ! empty( $cat_id ) ) {

            $html[] = '<li class="item-cat item-cat-' . $cat_id . ' item-cat-' . $cat_nicename . '"><a class="bread-cat bread-cat-' . $cat_id . ' bread-cat-' . $cat_nicename . '" href="' . $cat_link . '" title="' . $cat_name . '">' . $cat_name . '</a></li>';
            $html[] = '<li class="separator"> ' . $separator . ' </li>';
            $html[] = '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</strong></li>';

          } else {

            $link_text = ( $carrierproduct )? '<a href="' . get_the_permalink( $post->ID ) . '">' . get_the_title() . '</a>' : '<strong class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</strong>' ;
            $html[] = '<li class="item-current item-' . $post->ID . '">' . $link_text . '</li>';

          }

          if( $carrierproduct ){
            $html[] = '<li class="separator"> ' . $separator . ' </li>';
            $link_text = ucwords( str_replace('-', ' ', $carrierproduct ) );
            $search = ['Pdp', 'Mapd'];
            $replace = ['(PDP)','(MAPD)'];
            $link_text = str_replace( $search, $replace, $link_text );
            $html[] = '<li class="item-current"><strong class="bread-current">' . $link_text . '</strong></li>';
          }

        } else if ( is_category() ) {

            // Category page
            $html[] = '<li class="item-current item-cat"><strong class="bread-current bread-cat">' . single_cat_title('', false) . '</strong></li>';

        } else if ( is_page() ) {

            // Standard page
            if( $post->post_parent ){

              // If child page, get parents
              $anc = get_post_ancestors( $post->ID );

              // Get parents in the right order
              $anc = array_reverse($anc);

              // Parent page loop
              if ( !isset( $parents ) ) $parents = null;
              foreach ( $anc as $ancestor ) {
                $item_classes = [ 'item-parent', 'item-parent-' . $ancestor ];
                $children_subnav = get_subnav( $ancestor, $post->post_type );
                if( $children_subnav )
                  $item_classes[] = 'dropdown';
                $parents .= '<li class="' . implode( ' ', $item_classes ) . '"><a class="bread-parent bread-parent-' . $ancestor . '" href="' . get_permalink($ancestor) . '">' . get_the_title($ancestor) . '</a>' . $children_subnav . '</li>';
                $parents .= '<li class="separator separator-' . $ancestor . '"> ' . $separator . ' </li>';
              }

              // Display parent pages
              $html[] = $parents;

              // Get children
              $children_subnav = get_subnav( $post->ID, $post->post_type );

              // Current page
              $item_classes = [ 'item-current', 'item-' . $post->ID ];
              if( $children_subnav )
                $item_classes[] = 'dropdown';
              $html[] = '<li class="' . implode( ' ', $item_classes ) . '"><strong class="dropbtn"> ' . get_the_title() . '</strong>' . $children_subnav . '</li>';

            } else {
              $item_classes = [ 'item-current', 'item-' . $post->ID ];
              $anchor_classes = [ 'bread-current', 'bread-' . $post->ID ];
              $children_subnav = get_subnav( $post->ID, $post->post_type );
              if( $children_subnav ){
                $item_classes[] = 'dropdown';
                $anchor_classes[] = 'dropbtn';
              }

              // Just display current page if not parents
              $html[] = '<li class="' . implode( ' ', $item_classes ) . '"><strong class="' . implode( ' ', $anchor_classes ) . '"> ' . get_the_title() . '</strong>' . $children_subnav . '</li>';

            }

        } else if ( is_tag() ) {

            // Tag page

            // Get tag information
            $term_id        = get_query_var('tag_id');
            $taxonomy       = 'post_tag';
            $args           = 'include=' . $term_id;
            $terms          = get_terms( $taxonomy, $args );
            $get_term_id    = $terms[0]->term_id;
            $get_term_slug  = $terms[0]->slug;
            $get_term_name  = $terms[0]->name;

            // Display the tag name
            $html[] = '<li class="item-current item-tag-' . $get_term_id . ' item-tag-' . $get_term_slug . '"><strong class="bread-current bread-tag-' . $get_term_id . ' bread-tag-' . $get_term_slug . '">' . $get_term_name . '</strong></li>';

        } elseif ( is_day() ) {

            // Day archive

            // Year link
            $html[] = '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link( get_the_time('Y') ) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a></li>';
            $html[] = '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>';

            // Month link
            $html[] = '<li class="item-month item-month-' . get_the_time('m') . '"><a class="bread-month bread-month-' . get_the_time('m') . '" href="' . get_month_link( get_the_time('Y'), get_the_time('m') ) . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</a></li>';
            $html[] = '<li class="separator separator-' . get_the_time('m') . '"> ' . $separator . ' </li>';

            // Day display
            $html[] = '<li class="item-current item-' . get_the_time('j') . '"><strong class="bread-current bread-' . get_the_time('j') . '"> ' . get_the_time('jS') . ' ' . get_the_time('M') . ' Archives</strong></li>';

        } else if ( is_month() ) {

            // Month Archive

            // Year link
            $html[] = '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link( get_the_time('Y') ) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a></li>';
            $html[] = '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>';

            // Month display
            $html[] = '<li class="item-month item-month-' . get_the_time('m') . '"><strong class="bread-month bread-month-' . get_the_time('m') . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</strong></li>';

        } else if ( is_year() ) {

            // Display year archive
            $html[] = '<li class="item-current item-current-' . get_the_time('Y') . '"><strong class="bread-current bread-current-' . get_the_time('Y') . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</strong></li>';

        } else if ( is_author() ) {

            // Auhor archive

            // Get the author information
            global $author;
            $userdata = get_userdata( $author );

            // Display author name
            $html[] = '<li class="item-current item-current-' . $userdata->user_nicename . '"><strong class="bread-current bread-current-' . $userdata->user_nicename . '" title="' . $userdata->display_name . '">' . 'Author: ' . $userdata->display_name . '</strong></li>';

        } else if ( get_query_var('paged') ) {

            // Paginated archives
            $html[] = '<li class="item-current item-current-' . get_query_var('paged') . '"><strong class="bread-current bread-current-' . get_query_var('paged') . '" title="Page ' . get_query_var('paged') . '">'.__('Page') . ' ' . get_query_var('paged') . '</strong></li>';

        } else if ( is_search() ) {

            // Search results page
            $html[] = '<li class="item-current item-current-' . get_search_query() . '"><strong class="bread-current bread-current-' . get_search_query() . '" title="Search results for: ' . get_search_query() . '">Search results for: ' . get_search_query() . '</strong></li>';

        } elseif ( is_404() ) {

            // 404 page
            $html[] = '<li>' . 'Error 404' . '</li>';
        }

        $html[] = '</ul>';

    }

    return implode( '', $html );
}
add_shortcode( 'supercrumbs', __NAMESPACE__ . '\\custom_breadcrumbs' );

/**
 * Builds a dropdown of children of a page.
 *
 * @param      int          $post_parent_id  The post parent identifier
 *
 * @return     bool/string  The subnav.
 */
function get_subnav( $post_parent_id, $post_type = 'page' ){
  $children_subnav = false;

  // Get children
  $children = get_children([
      'post_parent'   => $post_parent_id,
      'order'         => 'ASC',
      'orderby'       => 'title',
      'post_type'     => $post_type,
  ]);
  if( $children ){

    foreach( $children as $child ){
      $item_classes = ['subnav-item'];
      if( is_page( $child->ID ) )
        $item_classes[] = 'item-current';
      $childrens[] = '<a class="' . implode( ' ', $item_classes ) . '" href="' . get_permalink( $child ) . '">' . get_the_title( $child ) . '</a>';
    }
    $children_subnav = '<div class="down-arrow"></div><div class="child-subnav dropdown-content">' . implode( '', $childrens ) . '</div>';
  }

  return $children_subnav;
}