<?php

namespace NCCAgent\shortcodes;

/**
 * Lists Carrier > Products in an accordion
 *
 * @param      array  $atts{
 *  @type   int   $post_id  The post ID.
 * }
 *
 * @return     string  HTML for the Carrier > Products accordion
 */
function acf_get_carrier_products( $atts ){
  $args = shortcode_atts( [
    'post_id' => null,
  ], $atts );

  $data = []; // Initialize the array we will pass to our handlebars templates.

  global $post;
  $post_id = ( is_null( $args['post_id'] ) )? $post->ID : $args['post_id'] ;

  $products = get_field( 'products' );
  if( empty( $products ) )
    return '';

  $carriername = get_the_title( $args['post_id'] );
  $data['carriername'] = $carriername;
  $data['year'] = date('Y');

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
    $data['products'][$x]['description'] = apply_filters( 'the_content', $product['product_details']['description'] );
    $data['products'][$x]['desc_review_date'] = $product['product_details']['desc_review_date'];
    $data['products'][$x]['medicare_product'] = ncc_is_medicare_product( $product_title );
    $data['products'][$x]['medicare_quote_engine_url'] = site_url( 'tools/medicare-quote-engine/' );
    $data['products'][$x]['states'] = ncc_build_state_chiclets( $product['product_details']['states'] );
    $data['products'][$x]['states_review_date'] = $product['product_details']['states_review_date'];
    $data['products'][$x]['plan_year'] = $product['product_details']['plan_year'];
    $data['products'][$x]['carriername'] = $carriername;
    $data['products'][$x]['kit_request_url'] = site_url('contracting/kit-request/');
    if( $product['product_details']['lower_issue_age'] && $product['product_details']['upper_issue_age'] ){
      $data['products'][$x]['lower_issue_age'] = $product['product_details']['lower_issue_age'];
      $data['products'][$x]['upper_issue_age'] = $product['product_details']['upper_issue_age'];
    }
    $x++;
  }

  $html = '';
  $html.= ncc_hbs_render_template( 'product-list-heading', $data );
  if( 3 <= count( $products ) ){
    $template = 'product-accordion';
    wp_enqueue_script( 'ncc-accordion' );
  } else {
    $template = 'product-list';
  }
  $html.= ncc_hbs_render_template( $template, $data );

  return $html;
}
add_shortcode( 'acf_carrier_products', __NAMESPACE__ . '\\acf_get_carrier_products' );

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
    'post_id' => null,
  ], $atts );

  global $post;
  $post_id = ( is_null( $args['post_id'] ) )? $post->ID : $args['post_id'] ;

  $carriers = get_field( 'carriers' );

  // Remove unpublished carriers
  foreach ($carriers as $key => $carrier) {
    if( 'publish' != $carrier->post_status )
      unset( $carriers[$key] );
  }

  usort( $carriers, function( $a, $b ){
    return strcmp( $a->post_title, $b->post_title );
  });

  if( empty( $carriers ) )
    return '<p><code>No carriers found for `' . get_the_title( $post_id ) . '`.</code></p>';

  $html = '';
  $html.= '<ul class="carriers">';

  foreach( $carriers as $carrier ){
    $logo = get_the_post_thumbnail_url( $carrier, 'full' );
    if( ! $logo || empty( $logo ) )
      $logo = plugin_dir_url( __FILE__ ) . '../img/placeholder_logo_800x450.png';
    $carriername = get_the_title( $carrier );
    $link = get_the_permalink( $post->ID ) . $carrier->post_name . '/';
    $html.= '<li><a href="' . $link . '"><img src="' . $logo . '" alt="' . $carriername . '" /></a><h3><a href="' . $link . '">' . $carriername . '</a></h3></li>';
  }
  $html.= '</ul>';

  return $html;
}
add_shortcode( 'acf_product_carriers', __NAMESPACE__ . '\\acf_get_product_carriers' );

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
  $admin_email = get_option( 'admin_email' );
  if( is_email( $admin_email ) )
    wp_mail( $admin_email, 'Invalid Carrier Name (' . $carrier_name . ')', wordwrap( $message, 80 ) );
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
        $headingEle = ( 1 < count( $products ) && empty( $carrierproduct ) )? 'h2' : 'h1';
        $html.= '<' . $headingEle . '>' . $carrier->post_title . ' ' . $product_name . '</' . $headingEle . '>';

        $chiclets = ncc_build_state_chiclets( $product_details['states'] );
        $html.= '<h2>State Availability</h2>';
        if( ! empty( $product_details['states_review_date'] ) ){
          $html.= '<p class="review-date">Current as of ' . $product_details['states_review_date'];
          if( ! empty( $product_details['plan_year'] ) )
            $html.= ' &ndash; <span class="plan-year">Plan Year ' . $product_details['plan_year'] . '</span>';
          $html.= '</p>';
        }
        $html.= $chiclets;

        $html.= '<h2>Plan Information</h2>';
        if( ! empty( $product_details['desc_review_date'] ) ){
          $html.= '<p class="review-date">Current as of ' . $product_details['desc_review_date'];
          if( ! empty( $product_details['plan_year'] ) )
            $html.= ' &ndash; <span class="plan-year">Plan Year ' . $product_details['plan_year'] . '</span>';
          $html.= '</p>';
        }

        $html.= $product_details['description'];
        // Product Kit Request CTA:
        $html.= '<div style="margin-bottom: 2em;">' . do_shortcode('[elementor-template id="2547"]') . '</div>';
        // Online Contracting CTA:
        $html.= '<div style="margin-bottom: 2em;">' . do_shortcode('[elementor-template id="2542"]') . '</div>';
        $html.= ncc_quick_links();
        return $html;
      }
    endwhile;
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
  if( get_field( 'product_finder_help_graphic', $post->ID ) )
    $help_graphic = get_field( 'product_finder_help_graphic', $post->ID );

  wp_enqueue_script( 'product-finder' );
  if( ! $called ){
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
 * Returns the WP logout URL, redirect defauts to `site_url()`.
 *
 * @param      array  $atts   The shortcode atts
 *
 * @return     string  The logout url.
 */
function logout_url( $atts ){
  $args = shortcode_atts([
    'redirect' => site_url(),
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

    if( 'marketing' == strtolower( $args['type'] ) ){
      $name = '<a href="' . $permalink . '">' . $name . '</a>';
      $photo = '<a href="' . $permalink . '">' . $photo . '</a>';
      $marketer_link = ncc_get_template([
        'template'  => 'team_member.marketer_link',
        'search'    => ['{permalink}','{firstname}'],
        'replace'   => [ $permalink, $firstname],
      ]);
    } else {
      $marketer_link = '';
    }

    $teamMemberFields = get_fields( $team_member->ID, false );
    $search = [ '{photo}', '{name}', '{title}', '{bio}', '{tel}', '{phone}', '{email}', '{marketer_link}' ];
    $phone = ( ! empty( $teamMemberFields['extension'] ) )? $teamMemberFields['phone'] . ' ext. ' . $teamMemberFields['extension'] : $teamMemberFields['phone'] ;
    $tel = ( ! empty( $teamMemberFields['extension'] ) )? $teamMemberFields['phone'] . ';ext=' . $teamMemberFields['extension'] : $teamMemberFields['phone'] ;
    $replace = [ $photo, $name, $teamMemberFields['title'], apply_filters( 'the_content', $teamMemberFields['bio'] ), $tel, $phone, $teamMemberFields['email'], $marketer_link ];
    $html.= str_replace( $search, $replace, $template );
  }

  return $html;
}
add_shortcode( 'team_member_list', __NAMESPACE__ . '\\team_member_list' );