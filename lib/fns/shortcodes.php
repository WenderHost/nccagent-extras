<?php

namespace NCCAgent\shortcodes;

/**
 * Lists Carrier > Products in an accordion
 *
 * @param      array  $atts{
 *   @type   int     $post_id  The post ID.
 *   @type   bool    $expanded When TRUE, shows expanded Product information.
 *   @type   string  $css_classes Space separated list of CSS classes to apply to the list.
 * }
 *
 * @return     string  HTML for the Carrier > Products accordion
 */
function acf_get_carrier_products( $atts ){
  $args = shortcode_atts( [
    'post_id'     => null,
    'expanded'    => false,
    'css_classes' => 'product-list',
  ], $atts );

  $data = []; // Initialize the array we will pass to our handlebars templates.
  $data['css_classes'] = $args['css_classes'];

  global $post;
  $post_id = ( is_null( $args['post_id'] ) )? $post->ID : $args['post_id'] ;

  if ( $args['expanded'] === 'false' ) $args['expanded'] = false;
  $args['expanded'] = (bool) $args['expanded'];

  $products = get_field( 'products' );
  if( empty( $products ) )
    return '';

  $carriername = get_the_title( $args['post_id'] );
  $data['carriername'] = $carriername;
  $data['year'] = date('Y');

  $requires_authentication = get_field( 'requires_authentication' );
  if( $requires_authentication  && ! is_user_logged_in() ){
    $html = ncc_hbs_render_template( 'product-list-heading', $data );
    $html.= ncc_get_alert([
      'title'       => 'REGISTRATION REQUIRED',
      'description' => 'Please <a href="' . home_url( 'login-register' ) . '">register or login</a> to view ' . $carriername . '\'s current products and state availability for ' . $data['year'] . '.',
    ]);
    return $html;
  }

  // Remove "unpublished" products from $products:
  foreach( $products as $key => $product ){
    if( ! is_object( $product['product'] ) || 'publish' != $product['product']->post_status )
      unset( $products[$key] );
  }

  $x = 1;
  foreach( $products as $product ){
    $product_title = ( ! empty( $product['product_details']['alternate_product_name'] ) )? $product['product_details']['alternate_product_name'] : $product['product']->post_title ;

    $data['products'][$x]['toggle_id'] = $product['product']->post_name . '-' . $x;
    $data['products'][$x]['permalink'] = get_the_permalink( $args['post_id'] ) . sanitize_title_with_dashes( $product_title ) . '/';
    $data['products'][$x]['title'] = $product_title;
    //$data['products'][$x]['description'] = wpautop( $product['product_details']['description'] );
    //$data['products'][$x]['desc_review_date'] = $product['product_details']['desc_review_date'];
    $data['products'][$x]['medicare_product'] = ncc_is_medicare_product( $product_title );
    $data['products'][$x]['medicare_quote_engine_url'] = home_url( 'tools/medicare-quote-engine/' );
    //$data['products'][$x]['states'] = ncc_build_state_chiclets( $product['product_details']['states'] );
    //$data['products'][$x]['states_review_date'] = $product['product_details']['states_review_date'];
    //$data['products'][$x]['plan_year'] = $product['product_details']['plan_year'];
    $data['products'][$x]['carriername'] = $carriername;
    $data['products'][$x]['kit_request_url'] = home_url('contracting/kit-request/');
    //if( $product['product_details']['lower_issue_age'] && $product['product_details']['upper_issue_age'] ){
      //$data['products'][$x]['lower_issue_age'] = $product['product_details']['lower_issue_age'];
      //$data['products'][$x]['upper_issue_age'] = $product['product_details']['upper_issue_age'];
    //}
    $x++;
  }

  $html = '';
  if( $args['expanded'] ){
    $html.= ncc_hbs_render_template( 'product-list-heading', $data );
    if( 3 <= count( $products ) ){
      $template = 'product-accordion';
      wp_enqueue_script( 'ncc-accordion' );
    } else {
      $template = 'product-list-expanded';
    }
  } else {
    $template = 'product-list';
  }
  $html.= ncc_hbs_render_template( $template, $data );

  return $html;
}
add_shortcode( 'acf_carrier_products', __NAMESPACE__ . '\\acf_get_carrier_products' );
add_shortcode( 'carrier_products', __NAMESPACE__ . '\\acf_get_carrier_products' );

/**
 * Displays a list of Carriers for a Product
 *
 * @param      array  $atts{
 *    @type   int   $post_id  The post ID.
 * }
 *
 * @return     string  HTML list of Carriers for the Product
 */
function acf_get_product_carriers( $atts ){
  $args = shortcode_atts( [
    'post_id'   => null,
    'textonly'  => false,
  ], $atts );

  global $post;
  $post_id = ( is_null( $args['post_id'] ) )? $post->ID : $args['post_id'] ;

  $carriers = get_field( 'carriers' );

  if ( $args['textonly'] === 'false' ) $args['textonly'] = false;
  $args['textonly'] = (bool) $args['textonly'];

  // Remove unpublished carriers
  if( is_array( $carriers ) ){
    foreach ($carriers as $key => $carrier) {
      if( 'publish' != $carrier->post_status )
        unset( $carriers[$key] );
    }
  }

  if( is_array( $carriers ) ):
    usort( $carriers, function( $a, $b ){
      return strcmp( $a->post_title, $b->post_title );
    });
  endif;

  if( empty( $carriers ) )
    return '<p><code>No carriers found for `' . get_the_title( $post_id ) . '`.</code></p>';

  $html = '';
  $html.= '<ul class="carriers">';

  if( $args['textonly'] ){
    $html = '<ul class="carriers-multi-column">';
    foreach( $carriers as $carrier ){
      $carriername = get_the_title( $carrier );
      $link = get_the_permalink( $carrier->ID );
      $html.= '<li><a href="' . $link . '">' . $carriername . '</a></li>';
    }
  } else {
    foreach( $carriers as $carrier ){
      $logo = get_the_post_thumbnail_url( $carrier, 'full' );
      if( ! $logo || empty( $logo ) )
        $logo = plugin_dir_url( __FILE__ ) . '../img/placeholder_logo_800x450.png';
      $carriername = get_the_title( $carrier );
      //$link = get_the_permalink( $post->ID ) . $carrier->post_name . '/';
      $link = get_the_permalink( $carrier->ID );
      $html.= '<li><a href="' . $link . '"><img src="' . $logo . '" alt="' . $carriername . '" /></a><h3><a href="' . $link . '">' . $carriername . '</a></h3></li>';
    }
  }


  $html.= '</ul>';

  return $html;
}
add_shortcode( 'acf_product_carriers', __NAMESPACE__ . '\\acf_get_product_carriers' );

/**
 * Displays the `[ncclistall]` shortcode.
 *
 * @param      array  $atts {
 *   @type  string  $type     The post type we are displaying. Can be either "carrier" or "product".
 *   @type  mixed   $depth    Set this to "1" to only show CPTs which are not children. Defaults to "all".
 *   @type  string  $exclude  Comma separated list of Post IDs for posts we want to exclude from the listing.
 * }
 *
 * @return     string  HTML display of all NCC Carrier or Product post types.
 */
function list_all( $atts ){
  $args = shortcode_atts( [
    'type' => 'carrier',
    'depth' => 'all',
    'exclude' => null,
  ], $atts );

  $post_type = ( ! in_array( $args['type'], ['carrier','product'] ) )? 'carrier' : $args['type'] ;

  $get_posts_args = [
    'post_type'       => $post_type,
    'posts_per_page'  => -1,
    'orderby'         => 'title',
    'order'           => 'ASC',
    'post_status'     => 'publish',
  ];

  if( 1 == $args['depth'] )
    $get_posts_args['post_parent'] = 0;

  if( ! empty( $args['exclude'] ) ){
    $exclude = ( stristr( $args['exclude'], ',' ) )? explode( ',', $args['exclude'] ) : [ $args['exclude'] ] ;
    $get_posts_args['exclude'] = $exclude;
  }

  $posts = get_posts( $get_posts_args );
  if( ! $posts )
    return ncc_get_alert( ['title' => 'No "' . $post_type . '" Posts Found', 'description' => 'No ' . $post_type . ' posts were found. Please add some via the admin.'] );

  $html = '';
  $html.= '<ul class="carriers">';

  foreach( $posts as $post ){
    $logo = plugin_dir_url( __FILE__ ) . '../img/placeholder_logo_800x450.png';
    if( has_post_thumbnail( $post->ID ) ){
      $thumbnail_id = get_post_thumbnail_id( $post->ID );
      $image_metadata = wp_get_attachment_metadata( $thumbnail_id );
      if( $image_metadata ){
        $width = $image_metadata['width'];
        $height = $image_metadata['height'];
      }
      if( 800 == $width && 450 == $height )
        $logo = get_the_post_thumbnail_url( $post->ID, 'full' );
    }
    $name = get_the_title( $post->ID );
    $link = get_the_permalink( $post->ID );
    $html.= '<li><a href="' . $link . '"><img src="' . $logo . '" alt="' . $name . '" /></a><h3><a href="' . $link . '">' . $name . '</a></h3></li>';
  }

  $html.= '</ul>';
  return $html;
}
add_shortcode( 'ncclistall', __NAMESPACE__ . '\\list_all' );

/**
 * Sets the excerpt length at 25 words.
 *
 * Used with `excerpt_length` filter.
 *
 * @return     integer  Returns the length of the excerpt.
 */
function custom_excerpt_length(){
  return 25;
}

/**
 * Returns the post excerpt.
 *
 * For use inside an Ele Custom Skin loop.
 *
 * @return     string  The excerpt.
 */
function custom_loop_post_excerpt(){
  add_filter( 'excerpt_length', __NAMESPACE__ . '\\custom_excerpt_length', 999 );
  $excerpt = get_the_excerpt();
  remove_filter( 'excerpt_length', __NAMESPACE__ . '\\custom_excerpt_length', 999 );
  return $excerpt;
}
add_shortcode( 'custom_loop_post_excerpt', __NAMESPACE__ . '\\custom_loop_post_excerpt' );

/**
 * Displays the Beamer embed HTML
 *
 * @return     string  The Beamer embed HTML
 */
function display_beamer(){
  $html = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/beamer.html' );
  return $html;
}
add_shortcode( 'beamer', __NAMESPACE__ . '\\display_beamer' );

/**
 * Displays a Carrier's products for a particular product.
 *
 * Example: Browsing `Cancer, Heart Attack, Stroke > Aetna`
 * will show all `Cancer, Heart Attack, Stroke` products
 * for `Aetna`.
 *
 * @return     string  HTML for Products by Carrier
 */
function productbycarrier(){
  global $post;

  $carrier_name = sanitize_title_with_dashes( get_query_var( 'productcarrier' ) );
  $args = [
    'name'        => $carrier_name,
    'post_type'   => 'carrier',
    'post_status' => 'publish',
    'numberposts' => 1,
  ];
  $carrier = get_posts($args);
  if( $carrier ){
    $carrier = $carrier[0];
    $products = get_field( 'products', $carrier->ID );
    if( have_rows( 'products', $carrier->ID ) ){
      $html = '';
      if( 0 < count( $products ) )
        $html.= '<h1>' . get_the_title( $post->ID ) . ' Products from ' . get_the_title( $carrier->ID ) . '</h1>';

      $urls = [];
      $list_items = [];
      while( have_rows( 'products', $carrier->ID ) ): the_row();
        $product = get_sub_field( 'product', true );
        // Display the product if it matches the Product page we're currently viewing
        if( $post->ID == $product->ID ){
          $product_details = get_sub_field('product_details');
          $product_name = ( ! empty( $product_details['alternate_product_name'] ) )? $product_details['alternate_product_name'] : $product->post_title ;
          $urls[] = get_permalink( $carrier->ID ) . sanitize_title_with_dashes( $product_name ) . '/';
          $list_items[] = '<a href="' . get_permalink( $carrier->ID ) . sanitize_title_with_dashes( $product_name ) . '/">' . $product_name . '</a>';
        }
      endwhile;

      if( 0 < count( $list_items ) )
        $html.= '<ul><li>' . implode('</li><li>', $list_items ) . '</li></ul>';
      return $html;
    }
    return '<p>No "' . $post->post_title . '" Products were found for ' . $carrier->post_title . '</p>';
  }

  $alert = ncc_get_alert([
    'title'       => 'Invalid Carrier Name (' . $carrier_name . ')',
    'description' => '<em>Oops! This is slightly embarassing.</em> The carrier name (i.e. <code>' . $carrier_name . '</code>) provided by the link you visited did not return any results from our database. Rest assured, our webmaster has been sent an alert, and we will get to the bottom of this issue.',
  ]);
  $message = "Bad URL: " . get_permalink( $post->ID ) . $carrier_name . "/\n\nA user tried to access the above URL, and no results were found. Please note the Carrier Name used in the URL:\n\n" . $carrier_name . "\n\nMore than likely, if that name does not correspond to the `slug` of a Carrier, then either some code or some HTML on the site has that carrier slug in a link.\n\nRegards,\n~MWENDER\n\nP.S. This message was generated by code on the NCC site.";
  //$admin_email = get_option( 'admin_email' );
  //if( is_email( $admin_email ) )
    //wp_mail( $admin_email, 'Invalid Carrier Name (' . $carrier_name . ')', wordwrap( $message, 80 ) );
  wp_mail( 'mwender@wenmarkdigital.com', 'Invalid Carrier Name (' . $carrier_name . ')', wordwrap( $message, 80 ) );
  return $alert;
}
add_shortcode( 'productbycarrier', __NAMESPACE__ . '\\productbycarrier' );

/**
 * Displays specific product information for a carrier.
 *
 * @param      <type>  $atts   The atts
 */
function carrierproduct(){
  global $post;
  $carrier = $post;
  $carrierproduct = sanitize_title_with_dashes( get_query_var( 'carrierproduct' ) );

  $post_type = get_post_type();
  if( 'carrier' != $post_type )
    return '';

  $products = get_field( 'products', $carrier->ID );
  if( have_rows( 'products', $carrier->ID ) && ! empty( $carrierproduct ) ){
    $html = '';
    while( have_rows( 'products' ) ): the_row();
      $product = get_sub_field( 'product' );
      $product_details = get_sub_field( 'product_details' );
      $product_name = ( ! empty( $product_details['alternate_product_name'] ) )? $product_details['alternate_product_name'] : $product->post_title ;
      if( strtolower( sanitize_title_with_dashes( $product_name ) ) == strtolower( $carrierproduct ) ){
        $data['headingElement'] = ( 1 < count( $products ) && empty( $carrierproduct ) )? 'h2' : 'h1';
        $data['carrier']['name'] = $carrier->post_title;
        $data['carrier']['product'] = $product_name;

        $data['product_details'] = $product_details;
        $data['states'] = ncc_build_state_chiclets( $product_details['states'] );
        $html.= ncc_hbs_render_template( 'carrier-product', $data );

        // Product Kit Request CTA:
        $html.= '<div style="margin-bottom: 2em;">' . do_shortcode('[elementor-template id="2547"]') . '</div>';
        // Online Contracting CTA:
        $html.= '<div style="margin-bottom: 2em;">' . do_shortcode('[elementor-template id="2542"]') . '</div>';
        $html.= ncc_quick_links();
        return $html;
      }
    endwhile;
    // We didn't have a Carrier > Product which matched $carrierproduct,
    // so we'll redirect back to the parent Carrier > Product. However,
    // we also have a hook to `template_redirect` which does a wp_redirect()
    // for invalid Carrier > Products, so this code shouldn't ever run:
    $carrier_name = get_the_title( $carrier->ID );
    $alert = ncc_get_alert(['type' => 'info', 'title' => 'Not Found', 'description' => 'I could not find ' . $carrier_name . ' &gt; `' . $carrierproduct . '`. Redirecting back to the main ' . $carrier_name . ' page.' ]);
    return $alert . '<script type="text/javascript">setTimeout( function(){window.location.replace(`' . get_permalink( $carrier->ID ) . '`);}, 3000);</script>';
  } else if ( have_rows( 'products', $carrier->ID ) && empty( $carrierproduct ) ){
    $product_list = [];
    while( have_rows( 'products' ) ): the_row();
      $product = get_sub_field( 'product' );
      $product_details = get_sub_field( 'product_details' );
      $product_name = ( ! empty( $product_details['alternate_product_name'] ) )? $product_details['alternate_product_name'] : $product->post_title ;
      $product_list[] = '<a href="' . get_permalink( $carrier->ID ) . sanitize_title_with_dashes( $product_name ) . '">' . $product_name . '</a>';
    endwhile;
    return '<p class="all-products-list"><strong>All ' .$carrier->post_title . ' Products:</strong> ' . implode( ', ', $product_list ) . '</p>';
  }

  //return '<p><code>[carrierproduct/]</code> shortcode. <code>carrierproduct = ' . $carrierproduct . '; $post_type = ' . $post_type . '; $carrier->ID = ' . $carrier->ID . '</code></p>';
}
add_shortcode( 'carrierproduct', __NAMESPACE__ . '\\carrierproduct' );

/**
 * Displays Products by State DataTable
 *
 * @param      array  $atts {
 *   @type  string  $table_id    Used for the HTML id attribute of the DataTable.
 *   @type  string  $table_class Uses for the class(es) to be output in the DataTable's HTML class attribute.
 * }
 *
 * @return     string  The DataTable HTML.
 */
function product_finder( $atts ){

  static $called = false;

  $args = shortcode_atts([
    'table_id' => 'datatable',
    'table_class' => '',
    'title' => true,
  ], $atts );
  if( $args['title'] === 'false' ) $args['title'] = false;
  $args['title'] = (bool) $args['title'];

  global $post;
  $help_graphic = '';
  if( get_field( 'product_finder_help_graphic', 'option') )
    $help_graphic = get_field( 'product_finder_help_graphic', 'option' );

  wp_enqueue_script( 'product-finder' );
  if( ! $called ){
    $product_finder_page_id = get_field('product_finder_page','option');
    $product_finder_url = get_permalink( $product_finder_page_id );
    $state_options = ncc_get_state_options();
    wp_localize_script( 'product-finder', 'wpvars', [
      'table_id'        => $args['table_id'],
      'table_class'     => $args['table_class'],
      'productFinderApi'   => rest_url( 'nccagent/v1/products' ),
      'helpGraphic'     => $help_graphic,
      'stateOptions'    => $state_options['options'],
      'stateOptionData' => $state_options['data'],
      'stateLibrary'    => $state_options['library'],
      'marketerUrl'     => rest_url( 'wp/v2/team_member/' ),
      'product_finder_slug' => str_replace( [home_url(),'/'], ['',''], $product_finder_url ),
    ]);
    $called = true;
  }

  $ie_alert = ncc_get_alert([
    'type'        => 'danger',
    'title'       => 'Your Browser is not Supported',
    'description' => 'Unfortunately, your browser is not supported. In order to use the Product Finder, please use one of these web browsers: <strong><a href="https://www.google.com/chrome/" target="_blank">Google Chrome</a></strong>, <strong><a href="https://www.mozilla.org/en-US/firefox/new/" target="_blank">Mozilla Firefox</a></strong>, or <strong><a href="https://www.microsoft.com/en-us/edge" target="_blank">Microsoft Edge</a></strong>.',
    'css_classes' => 'internet-explorer',
  ]);

  /**
   * We're including the IE note twice because we need to use 2 methods
   * to display an IE conditional since conditional tags were dropped
   * in IE 10:
   *
   * - First method uses a media query found in lib/scss/_datatables_mods.scss
   * - Second uses standard IE conditional tags available in IE <= 9.X.X.
   *
   * Reference: https://www.mediacurrent.com/blog/pro-tip-how-write-conditional-css-ie10-and-11/
   */
  $ie_alert = $ie_alert . '<!--[if lte IE 9 ]>' . $ie_alert . '<![endif]-->';

  if( $args['title'] ){
    $title = '<h2 style="margin: 0;">Product Finder</h2>';
    $style = '';
  } else {
    $title = '<span>&nbsp;</span>';
    $style = ' style="margin-bottom: 0;"';
  }

  return $ie_alert . '<div class="product-finder-header"' . $style . '>' . $title . '<a href="#" id="reset-form">reset form</a></div><table class="' . $args['table_class'] . '" id="' . $args['table_id']. '"><thead><tr><th class="label" style="width: 40px; font-size: 12px; padding: 4px;">Make your selection(s):</th><th style="width: 40%">States</th><th style="width: 30%">Product</th><th style="width: 30%">Carrier</th><th id="selectors">&nbsp;</th></tr><tr><th id="ncc-staff" colspan="5"></th></tr></thead><tbody></tbody></table>';
}
add_shortcode( 'productsbystate', __NAMESPACE__ . '\\product_finder' );

/**
 * Displays post content while adding a "Read more..." link after a `/more` tag
 *
 * @return     string  HTML content with Read More applied.
 */
function readmore_content(){
  global $post;

  $post_content = $post->post_content;
  if( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() )
    return $post_content;

  $split_content = get_extended( $post_content );
  if( ! empty( $split_content['extended'] ) ){
    $hidetext_js = "\n" . '<script type="text/javascript">hideTextVars = {"carrier": "' . get_the_title( $post->ID ) . '"}</script>';
    $hidetext_js.= "\n" . '<script type="text/javascript">' . file_get_contents( plugin_dir_path( __FILE__ ) . '../js/hidetext.js' ) . '</script>';

    return $split_content['main'] . '<div class="hidetext">' . $split_content['extended'] . '</div>' . $hidetext_js;
  } else {
    return $post_content;
  }
}
add_shortcode( 'readmore_content', __NAMESPACE__ . '\\readmore_content' );

/**
 * Returns the WP logout URL, redirect defauts to `home_url()`.
 *
 * @param      array  $atts   The shortcode atts
 *
 * @return     string  The logout url.
 */
function logout_url( $atts ){
  $args = shortcode_atts([
    'redirect' => home_url(),
  ], $atts );
  return wp_logout_url( $args['redirect'] );
}
add_shortcode( 'logouturl', __NAMESPACE__ . '\\logout_url' );

/**
 * Returns a list of team members
 *
 * @param      array  $atts {
 *   @type   string   $type The type of team member (e.g. marketer, administrative, etc)
 * }
 *
 * @return     string  HTML for Team Member list
 */
function team_member_list( $atts ){
  $args = shortcode_atts([
    'type' => null
  ], $atts );

  $query_args = [
    'numberposts' => -1,
    'post_type'   => 'team_member',
    'orderby'     => 'menu_order',
    'order'       => 'ASC',
  ];
  if( ! is_null( $args['type'] ) ){
    $type = get_term_by( 'slug', strtolower( $args['type'] ), 'staff_type' );
    if( ! $type )
      return '<p><strong>No `' . $args['type'] . '` Staff Type</strong><br>We could not locate the Staff Type you entered. Please check your spelling, and make sure the <code>type</code> you entered matches one of the Staff Types in the admin.</p>';

    $query_args['tax_query'] = [
      [
        'taxonomy'  => 'staff_type',
        'field'     => 'slug',
        'terms'     => $args['type'],
      ]
    ];
  }

  $team_members = get_posts( $query_args );

  if( ! $team_members )
    return '<p><strong>No Team Members Found</strong><br/>No Team Members found. Please check your shortcode parameters.</p>';

  $html = '';
  $template = ncc_get_template('team_member');
  foreach( $team_members as $team_member ){
    $photo = '<div class="photo" style="background-image: url(' . get_the_post_thumbnail_url( $team_member->ID, 'large' ) . ')"></div>';

    $name = $team_member->post_title;
    $name_array = explode( ' ', $name );
    $lastname = array_pop( $name_array );
    $firstname = implode( ' ', $name_array );
    $permalink = get_permalink( $team_member->ID );

    $calendar_html = '';
    if( 'marketing' == strtolower( $args['type'] ) ){
      $marketerFields = get_fields( $team_member->ID, false );
      $marketerFields['hubspot'] = get_field( 'hubspot', $team_member->ID );
      if( array_key_exists( 'calendar_link', $marketerFields['hubspot'] ) && ! empty( $marketerFields['hubspot']['calendar_link'] ) ){
        $calendar_html = '<li class="elementor-icon-list-item">
          <a href="' . $marketerFields['hubspot']['calendar_link'] . '" style="text-decoration: none;" target="_blank">
            <span class="elementor-icon-list-icon"><i aria-hidden="true" class="fas fa-calendar-alt"></i></span><span class="elementor-icon-list-text"> Schedule a Meeting with ' . $firstname . '</span>
          </a>
        </li>';
      }
    }

    $teamMemberFields = get_fields( $team_member->ID, false );
    $search = [ '{photo}', '{name}', '{title}', '{bio}', '{tel}', '{phone}', '{email}', '{calendar_html}' ];
    $phone = ( ! empty( $teamMemberFields['extension'] ) )? $teamMemberFields['phone'] . ' ext. ' . $teamMemberFields['extension'] : $teamMemberFields['phone'] ;
    $tel = ( ! empty( $teamMemberFields['extension'] ) )? $teamMemberFields['phone'] . ';ext=' . $teamMemberFields['extension'] : $teamMemberFields['phone'] ;
    $replace = [ $photo, $name, $teamMemberFields['title'], wpautop( $teamMemberFields['bio'] ), $tel, $phone, $teamMemberFields['email'], $calendar_html ];
    $html.= str_replace( $search, $replace, $template );
  }

  return $html;
}
add_shortcode( 'team_member_list', __NAMESPACE__ . '\\team_member_list' );