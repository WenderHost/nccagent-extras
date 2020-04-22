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

  global $post;
  $post_id = ( is_null( $args['post_id'] ) )? $post->ID : $args['post_id'] ;

  $products = get_field( 'products' );
  if( empty( $products ) )
    return '';

  $html = '<h3>' . get_the_title( $args['post_id'] ) . ' Products and State Availability</h3>';
  $html.= '<div class="product-content"><p>These are ' . get_the_title( $args['post_id'] ) . '\'s current products and state availability for ' . date('Y') . ', as well as information on contracting and appointment.</p></div>';

  if( 3 > count( $products ) ){
    foreach( $products as $product ){
      if( 'publish' != $product['product']->post_status )
        continue;

      $product_title = ( ! empty( $product['product_details']['alternate_product_name'] ) )? $product['product_details']['alternate_product_name'] : $product['product']->post_title ;
      $states = ( is_array( $product['product_details']['states'] ) )? '<span class="chiclet">' . implode('</span> <span class="chiclet">', $product['product_details']['states'] ) . '</span>' : $product['product_details']['states'] ;
      $html.= '<h4 class="product-title">' . $product_title . '</h4><p>' . $states . '</p>';
      $html.= '<div class="product-content">' . apply_filters( 'the_content', $product['product_details']['description'] ) . '<p>Permalink: <a href="'. get_the_permalink( $args['post_id']) . sanitize_title_with_dashes( $product_title ) . '">' . get_the_title( $args['post_id'] ) . ' ' . $product_title . '</a></p></div>';
    }
  } else {
    if( NCC_DEV_ENV ){
      wp_enqueue_script('ncc-accordion' );
    } else {
      $accordion_js = file_get_contents( plugin_dir_path( __FILE__ ) . '../js/accordion.js' );
      $html.= '<script type="text/javascript">' . $accordion_js . '</script>';
    }

    $accordion_html = ncc_get_template(['template' => 'accordion.html']);
    $x = 1;
    foreach( $products as $product ){
      if( 'publish' != $product['product']->post_status )
        continue;

      $product_title = ( ! empty( $product['product_details']['alternate_product_name'] ) )? $product['product_details']['alternate_product_name'] : $product['product']->post_title ;
      $product_description = apply_filters( 'the_content', $product['product_details']['description'] );
      $states = ( is_array( $product['product_details']['states'] ) )? '<span class="chiclet">' . implode('</span> <span class="chiclet">', $product['product_details']['states'] ) . '</span>' : $product['product_details']['states'] ;

      $search = [
        '{{toggle_id}}',
        '{{toggle_title}}',
        '{{states}}',
        '{{description}}',
        '{{permalink}}',
        '{{link_text}}',
      ];
      $replace = [
        $product['product']->post_name . '-' . $x,
        $product_title,
        $states,
        $product_description,
        get_the_permalink( $args['post_id']) . sanitize_title_with_dashes( $product_title ),
        'View this information as a web page.',
      ];
      $html.= str_replace( $search, $replace, $accordion_html );
      $x++;
    }
    $html = '<div class="accordion">' . $html . '</div>';
  }

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
  usort( $carriers, function( $a, $b ){
    return strcmp( $a->post_title, $b->post_title );
  });

  if( empty( $carriers ) )
    return '<p><code>No carriers found for `' . get_the_title( $post_id ) . '`.</code></p>';

  $html = '';
  $html.= '<ul class="carriers">';

  foreach( $carriers as $post ){
    setup_postdata( $post );
    $logo = get_the_post_thumbnail_url( $post, 'full' );
    if( ! $logo || empty( $logo ) )
      $logo = plugin_dir_url( __FILE__ ) . '../img/placeholder_logo_800x450.png';
    $html.= '<li><a href="' . get_the_permalink( $post_id ) . $post->post_name . '"><img src="' . $logo . '" alt="' . get_the_title() . '" /></a><h3><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h3></li>';
  }
  $html.= '</ul>';

  return $html;
}
add_shortcode( 'acf_product_carriers', __NAMESPACE__ . '\\acf_get_product_carriers' );

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
      if( 1 < count( $products ) )
        $html.= '<h1>' . get_the_title( $post->ID ) . ' Products from ' . get_the_title( $carrier->ID ) . '</h1>';

      $urls = [];
      $list_items = [];
      while( have_rows( 'products', $carrier->ID ) ): the_row();
        $product = get_sub_field( 'product', true );
        // Display the product if it matches the Product page we're currently viewing
        if( $post->ID == $product->ID ){
          $product_details = get_sub_field('product_details');
          $product_name = ( ! empty( $product_details['alternate_product_name'] ) )? $product_details['alternate_product_name'] : $product->post_title ;
          //$product_description = $product_details['description'];
          //$product_states = $product_details['states'];

          //$states = ( is_array( $product_states ) )? '<span class="chiclet">' . implode('</span> <span class="chiclet">', $product_states ) . '</span>' : $product_states ;

          //$headingEle = ( 1 < count( $products ) )? 'h2' : 'h1';

          //$html.= '<' . $headingEle . '>' . $carrier->post_title . ' ' . $product_name . '</' . $headingEle . '><p><code>' . $states . '</code></p>' . $product_description;
          $urls[] = get_permalink( $carrier->ID ) . sanitize_title_with_dashes( $product_name );
          $list_items[] = '<a href="' . get_permalink( $carrier->ID ) . sanitize_title_with_dashes( $product_name ) . '">' . $product_name . '</a>';
        }
      endwhile;
      if( 1 === count( $urls ) )
        wp_redirect( $urls[0], 302 );

      if( 0 < count( $list_items ) )
        $html.= '<ul><li>' . implode('</li><li>', $list_items ) . '</li></ul>';
      return $html;
    }
    return '<pre>No product details were found for ' . $carrier->post_title . ' &gt; ' . $post->post_title . '</pre>';
  }

  return '<code>$carrier_name = ' . $carrier_name . '</code>';
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
        $product_description = $product_details['description'];
        $product_states = $product_details['states'];

        $states = ( is_array( $product_states ) )? '<span class="chiclet">' . implode('</span> <span class="chiclet">', $product_states ) . '</span>' : $product_states ;

        $headingEle = ( 1 < count( $products ) && empty( $carrierproduct ) )? 'h2' : 'h1';

        $html.= '<' . $headingEle . '>' . $carrier->post_title . ' ' . $product_name . '</' . $headingEle . '><p>' . $states . '</p>' . $product_description;
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
 * @param      <type>  $atts   The atts
 *
 * @return     string  ( description_of_the_return_value )
 */
function product_finder( $atts ){

  static $called = false;

  $args = shortcode_atts([
    'table_id' => 'datatable',
    'table_class' => '',
  ], $atts );

  global $post;
  $help_graphic;
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

  return '<div class="product-finder-header"><h5>Product Finder</h5><a href="#" id="reset-form">reset form</a></div><table class="' . $args['table_class'] . '" id="' . $args['table_id']. '"><thead><tr><th class="label" style="width: 40px; font-size: 12px; padding: 4px;">Make your selection(s):</th><th style="width: 40%">States</th><th style="width: 30%">Product</th><th style="width: 30%">Carrier</th><th id="selectors">&nbsp;</th></tr><tr><th id="ncc-staff" colspan="5"></th></tr></thead><tbody></tbody></table>';
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
  //$template = file_get_contents( plugin_dir_path( __FILE__ ) . '../html/team_member.html' );
  $template = ncc_get_template('team_member');
  foreach( $team_members as $team_member ){
    $photo = get_the_post_thumbnail_url( $team_member->ID, 'large' );
    $name = $team_member->post_title;

    if( 'marketing' == strtolower( $args['type'] ) )
      $name = '<a href="' . get_permalink( $team_member->ID ) . '">' . $name . '</a>';

    $teamMemberFields = get_fields( $team_member->ID, false );
    $search = [ '{photo}', '{name}', '{title}', '{bio}', '{tel}', '{phone}', '{email}' ];
    $phone = ( ! empty( $teamMemberFields['extension'] ) )? $teamMemberFields['phone'] . ' ext. ' . $teamMemberFields['extension'] : $teamMemberFields['phone'] ;
    $tel = ( ! empty( $teamMemberFields['extension'] ) )? $teamMemberFields['phone'] . ';ext=' . $teamMemberFields['extension'] : $teamMemberFields['phone'] ;
    $replace = [ $photo, $name, $teamMemberFields['title'], apply_filters( 'the_content', $teamMemberFields['bio'] ), $tel, $phone, $teamMemberFields['email'] ];
    $html.= str_replace( $search, $replace, $template );
  }

  return $html;
}
add_shortcode( 'team_member_list', __NAMESPACE__ . '\\team_member_list' );