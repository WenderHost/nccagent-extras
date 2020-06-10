<?php

namespace NCCAgent\breadcrumbs;

/**
 * Breadcrumbs with dropdown menus for child pages
 *
 * @param      array  $atts   The atts
 */
function custom_breadcrumbs( $atts ) {
    $args = shortcode_atts([
        'separator'         => '/',
        'breadcrumbs_id'    => 'breadcrumbs',
        'breadcrumbs_class' => 'breadcrumbs',
        'home_title'        => 'Home',
    ], $atts );

    // Settings
    $separator          = $args['separator'];
    $separator_html     = '<li class="separator separator-home"> ' . $separator . ' </li>';
    $breadcrumbs_id     = $args['breadcrumbs_id'];
    $breadcrumbs_class  = $args['breadcrumbs_class'];
    $home_title         = $args['home_title'];
    $html               = [];

    $carrierproduct = sanitize_title_with_dashes( get_query_var( 'carrierproduct' ) );
    $productcarrier = sanitize_title_with_dashes( get_query_var( 'productcarrier' ) );

    // If you have any custom post types with custom taxonomies, put the taxonomy name below (e.g. product_cat)
    $custom_taxonomy    = 'product_cat';

    // Get the query & post information
    global $post,$wp_query;

    // Do not display on the homepage
    if ( ! is_front_page() ) {

        // Build the breadcrums
        $html[] = '<ul id="' . $breadcrumbs_id . '" class="' . $breadcrumbs_class . '">';

        // Home page
        $html[] = '<li class="item-home"><a class="bread-link bread-home" href="' . get_home_url() . '" title="' . $home_title . '"><i class="fas fa-home"></i></a></li>';
        $html[] = $separator_html;

        if ( is_archive() && !is_tax() && !is_category() && !is_tag() ) {

            $html[] = '<li class="item-current item-archive"><span class="bread-current bread-archive">' . post_type_archive_title($prefix, false) . '</span></li>';

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
            $html[] = '<li class="item-current item-archive"><span class="bread-current bread-archive">' . $custom_tax_name . '</span></li>';

        } else if ( is_single() ) {

          // If post is a custom post type
          $post_type = get_post_type();

          // If it is a custom post type display name and link
          if( $post_type != 'post' ) {

            $post_type_object = get_post_type_object( $post_type );
            $post_type_archive = get_post_type_archive_link( $post_type );
            $item_classes = [ 'item-cat', 'item-custom-post-type-' . $post_type ];

            if( 'Carriers' == $post_type_object->labels->name || 'Products' == $post_type_object->labels->name ){
              $link_text = 'Carriers &amp; Products';
              $post_type_archive = site_url( 'plans' );
              /**
               * 03/27/2020 - Uncomment the folloiwng lines to add a drop down of Carriers to the "Carriers & Products"
               * breadcrumb. To really make this work, I need to code something to appropriately handle the list of
               * products and carriers such that we don't have too large of a dropdown.
               */
              //$children_subnav = get_subnav( 0, 'carrier' );
              //$item_classes[] = 'dropdown';
            } else {
              $link_text = $post_type_object->labels->name;
            }

            if( 'team_member' == $post_type ){
              $about_page = get_page_by_path( 'about' );
              $children_subnav = get_subnav( $about_page->ID );
              $html[] = '<li class="item-parent item-parent-' . $about_page->ID . ' dropdown"><div><a href="' . get_permalink( $about_page->ID ) . '">About</a>' . $children_subnav . '</div></li>';
              $html[] = '<li class="separator"> ' . $separator . ' </li>';
              $staff_page = get_page_by_path( 'about/staff' );
              $html[] = '<li class="item-parent"><div><a href="' . get_permalink( $staff_page->ID ) . '">Staff</a></div></li>';
            } else {
              $html[] = '<li class="' . implode( ' ', $item_classes ) . '"><div><a class="bread-cat bread-custom-post-type-' . $post_type . '" href="' . $post_type_archive . '">' . $link_text . '</a></div></li>';
            }

            if( ( 'carrier' == $post_type || 'product' == $post_type ) && ( ! empty( $carrierproduct ) || ! empty( $productcarrier ) ) ){
              $html[] = '<li class="separator"> ' . $separator . ' </li>';
              $html[] = '<li class="' . implode( ' ', $item_classes ) . '"><div><a class="bread-cat bread-custom-post-type-' . $post_type . '" href="' . get_permalink( $post ) . '">' . get_the_title( $post ) . '</a></div></li>';
            }
          } else {
            // Add a link to the "Blog" when we're on a post single.
            $html[] = '<li><a href="' . site_url( 'blog/' ) .'">Blog</a></li>';
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
            $cat_display = [];
            foreach($cat_parents as $parents) {
              $cat_display[] = '<li class="item-cat">'.$parents.'</li>';
              $cat_display[] = '<li class="separator"> ' . $separator . ' </li>';
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
            array_pop( $cat_display );
            $html[] = implode( '', $cat_display );

            //$html[] = '<li class="item-current item-' . $post->ID . '"><span class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</span></li>';

          // Else if post is in a custom taxonomy
          } else if( ! empty( $cat_id ) ) {

            $html[] = '<li class="item-cat item-cat-' . $cat_id . ' item-cat-' . $cat_nicename . '"><a class="bread-cat bread-cat-' . $cat_id . ' bread-cat-' . $cat_nicename . '" href="' . $cat_link . '" title="' . $cat_name . '">' . $cat_name . '</a></li>';
            $html[] = '<li class="separator"> ' . $separator . ' </li>';
            $html[] = '<li class="item-current item-' . $post->ID . '"><span class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</span></li>';

          } else {

            //$link_text = ( $carrierproduct || $productcarrier )? '<a href="' . get_the_permalink( $post->ID ) . '">' . get_the_title() . '</a>' : '<span class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</span>' ;
            //$html[] = '<li class="item-current item-' . $post->ID . '">' . $link_text . '</li>';

          }
          /*
          if( $carrierproduct || $productcarrier ){
            $html[] = '<li class="separator"> ' . $separator . ' </li>';
            $link_text = ( $carrierproduct )? $carrierproduct : $productcarrier ;
            $link_text = ucwords( str_replace('-', ' ', $link_text ) );
            $search = ['Pdp', 'Mapd'];
            $replace = ['(PDP)','(MAPD)'];
            $link_text = str_replace( $search, $replace, $link_text );
            $html[] = '<li class="item-current"><span class="bread-current">' . $link_text . '</span></li>';
          }
          /**/

        } else if ( is_category() ) {

            // Category page
            $html[] = '<li><a href="' . site_url( 'blog/' ) . '">Blog</a></li>';
            $html[] = $separator_html;
            $html[] = '<li class="item-current item-cat"><span class="bread-current bread-cat">' . single_cat_title('', false) . '</span></li>';

        } else if ( is_page() ) {

            // Standard page
            if( $post->post_parent ){

              // If child page, get parents
              $anc = get_post_ancestors( $post->ID );

              // Get parents in the right order
              $anc = array_reverse($anc);

              // Parent page loop
              if ( ! isset( $parents ) ) $parents = [];
              foreach ( $anc as $ancestor ) {
                $item_classes = [ 'item-parent', 'item-parent-' . $ancestor ];
                $children_subnav = get_subnav( $ancestor, $post->post_type );
                if( $children_subnav )
                  $item_classes[] = 'dropdown';

                //$parents .=
                $title = get_the_title($ancestor);
                $link_text = ( 'About National Contracting Center' == $title )? 'About' : get_the_title($ancestor) ;
                $parents[] = '<li class="' . implode( ' ', $item_classes ) . '"><div><a class="bread-parent bread-parent-' . $ancestor . '" href="' . get_permalink($ancestor) . '">' . $link_text . '</a>' . $children_subnav . '</div></li>';
                //$parents .=
                $parents[] = '<li class="separator separator-' . $ancestor . '"> ' . $separator . ' </li>';
              }

              // Display parent pages
              //$html[] = $parents;
              $html = array_merge( $html, $parents );

              // Get children
              $children_subnav = get_subnav( $post->ID, $post->post_type );

              // Current page
              $item_classes = [ 'item-current', 'item-' . $post->ID ];
              if( $children_subnav )
                $item_classes[] = 'dropdown';

              // Only add the current page if it has child pages
              if( in_array( 'dropdown', $item_classes ) ){
                $title = get_the_title();
                $link_text = ( 'About National Contracting Center' == $title )? 'About' : $title;
                $html[] = '<li class="' . implode( ' ', $item_classes ) . '"><div><span class="dropbtn"> ' . $link_text . '</span>' . $children_subnav . '</div></li>';
              } else {
                // If our current page doesn't have child pages, remove the trailing seperator
                array_pop( $html );
              }

            } else {
              $item_classes = [ 'item-current', 'item-' . $post->ID ];
              $anchor_classes = [ 'bread-current', 'bread-' . $post->ID ];
              $children_subnav = get_subnav( $post->ID, $post->post_type );
              if( $children_subnav ){
                $item_classes[] = 'dropdown';
                $anchor_classes[] = 'dropbtn';
              }

              // Just display current page if not parents
              $title = get_the_title();
              /**
               * SPECIAL CASES
               *
               * On some pages we want to replace what would normally
               * appear as the last item in the breadcrumb:
               */
              $link_text = $title;
              switch( $title ){
                case 'About National Contracting Center':
                  $link_text = 'About';
                  break;

                case 'Products':
                case 'Carriers':
                  $link_text = '<a href="' . site_url( 'plans/' ) . '">Carriers &amp; Products</a>';
                  break;

                case 'Carriers &#038; Products':
                case 'Carriers & Products':
                case 'Carriers &amp; Products':
                  //ncc_error_log('🔔 $children_subnav = ' . $children_subnav );
                  break;

                default:
                  // nothing
              }
              $html[] = '<li class="' . implode( ' ', $item_classes ) . '"><div><span class="' . implode( ' ', $anchor_classes ) . '"> ' . $link_text . '</span>' . $children_subnav . '</div></li>';
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
            $html[] = '<li class="item-current item-tag-' . $get_term_id . ' item-tag-' . $get_term_slug . '"><span class="bread-current bread-tag-' . $get_term_id . ' bread-tag-' . $get_term_slug . '">' . $get_term_name . '</span></li>';

        } elseif ( is_day() ) {

            // Day archive

            // Year link
            $html[] = '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link( get_the_time('Y') ) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a></li>';
            $html[] = '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>';

            // Month link
            $html[] = '<li class="item-month item-month-' . get_the_time('m') . '"><a class="bread-month bread-month-' . get_the_time('m') . '" href="' . get_month_link( get_the_time('Y'), get_the_time('m') ) . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</a></li>';
            $html[] = '<li class="separator separator-' . get_the_time('m') . '"> ' . $separator . ' </li>';

            // Day display
            $html[] = '<li class="item-current item-' . get_the_time('j') . '"><span class="bread-current bread-' . get_the_time('j') . '"> ' . get_the_time('jS') . ' ' . get_the_time('M') . ' Archives</span></li>';

        } else if ( is_month() ) {

            // Month Archive

            // Year link
            $html[] = '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link( get_the_time('Y') ) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a></li>';
            $html[] = '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>';

            // Month display
            $html[] = '<li class="item-month item-month-' . get_the_time('m') . '"><span class="bread-month bread-month-' . get_the_time('m') . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</span></li>';

        } else if ( is_year() ) {

            // Display year archive
            $html[] = '<li class="item-current item-current-' . get_the_time('Y') . '"><span class="bread-current bread-current-' . get_the_time('Y') . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</span></li>';

        } else if ( is_author() ) {

            // Auhor archive

            // Get the author information
            global $author;
            $userdata = get_userdata( $author );

            // Display author name
            $html[] = '<li class="item-current item-current-' . $userdata->user_nicename . '"><span class="bread-current bread-current-' . $userdata->user_nicename . '" title="' . $userdata->display_name . '">' . 'Author: ' . $userdata->display_name . '</span></li>';

        } else if ( get_query_var('paged') ) {

            // Paginated archives
            $html[] = '<li><a href="' . site_url('blog/') . '">Blog</a></li>';
            $html[] = $separator_html;
            $html[] = '<li class="item-current-' . get_query_var('paged') . '"><span class="bread-current bread-current-' . get_query_var('paged') . '" title="Page ' . get_query_var('paged') . '">'.__('Page') . ' ' . get_query_var('paged') . '</span></li>';

        } else if ( is_search() ) {

            // Search results page
            $html[] = '<li class="item-current item-current-' . get_search_query() . '"><span class="bread-current bread-current-' . get_search_query() . '" title="Search results for: ' . get_search_query() . '">Search results for: ' . get_search_query() . '</span></li>';

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
  global $post;
  $current_slug = $post->post_name;


  $children_subnav = false;
  $parent = get_post( $post_parent_id );
  $parent_slug = $parent->post_name;

  // Get children
  $args = [
      'post_parent'   => $post_parent_id,
      'order'         => 'ASC',
      'orderby'       => 'title',
      'post_type'     => $post_type,
  ];

  $children = get_children( $args );
  $carriers_and_products_parents = ['plans','carriers','products'];
  if( $children || in_array( $current_slug, $carriers_and_products_parents ) ){
    if( in_array( $parent_slug, $carriers_and_products_parents ) || in_array( $current_slug, $carriers_and_products_parents ) ){
      $item_classes = ['subnav-item'];
      $plans_children = [
        'Product Finder'          => site_url( 'plans/' ),
        'All Carriers'            => site_url( 'carriers/' ),
        'Medicare Advantage'      => site_url( 'product/medicare-advantage/' ),
        'Medicare Supplement'     => site_url( 'product/medicare-supplement/' ),
        'Prescription Drug Plan'  => site_url( 'product/medicare-pdp/' ),
        'Anncillaries'            => site_url( 'product/ancillaries' ),
      ];
      foreach( $plans_children as $title => $permalink ){
        $childrens[] = '<a class="' . implode( ' ', $item_classes ) . '" href="' . $permalink . '">' . $title . '</a>';
      }
    } else {
      foreach( $children as $child ){
        $item_classes = ['subnav-item'];
        if( is_page( $child->ID ) )
          $item_classes[] = 'item-current';
        $title = get_the_title( $child );
        $search = ['Black Book: Agent Resources'];
        $title = str_replace( $search, '', $title );
        $childrens[] = '<a class="' . implode( ' ', $item_classes ) . '" href="' . get_permalink( $child ) . '">' . $title . '</a>';
      }
    }
    $children_subnav = '<div class="down-arrow"></div><div class="child-subnav dropdown-content">' . implode( '', $childrens ) . '</div>';
  }

  return $children_subnav;
}