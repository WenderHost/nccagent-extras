<?php

namespace NCCAgent\dirlister;

/**
 * Displays the Carrier Document Library.
 *
 * @return     string  HTML for the Carrier Document Library.
 */
function dirlister(){
  wp_enqueue_script( 'dirlister' );
  global $post;

  if( get_query_var( 'path' ) ){
    $path = get_query_var( 'path' );
  } else {
    $path = get_field( 'vpn_link', $post->ID );
  }

  if( ! $path )
    $path = 'https://vpn.ncc-agent.com/docs/';

  wp_localize_script( 'dirlister', 'wpvars', [
    'self' => get_permalink( $post ),
    'path' => $path,
    'endpoint' => rest_url('nccagent/v1/dirlister'),
    'nonce' => wp_create_nonce( 'wp_rest' ) ]
  );
  if( is_user_logged_in() )
    $alert = ncc_get_alert(['type' => 'info', 'title' => null, 'description' => '<strong>Note:</strong> Do not use your browser\'s Back button. That will take you back to the previous page you visited. Instead, use the links and the Back button.']);
  return '<div id="dirlister"><h1>Carrier Document Library</h1>' . $alert . '<a class="doc-link button" id="back-button" aira-type="dir" href="">&larr; Back</a><h5>...</h5><ul class="directory-listing"><li class="message">Loading directory...</li></ul></div>';
}
add_shortcode( 'dirlister', __NAMESPACE__ . '\\dirlister' );

/**
 * Displays the button used to open a Carrier's Document Library.
 *
 * @return     string  HTML for displaying a Carrier Document Library button.
 */
function dirlister_button(){
  global $post;
  $vpn_link = get_field( 'vpn_link', $post->ID );

  if( empty( $vpn_link ) )
    return '<p><code>ERROR: No`vpn_link` found.</code></p>';

  $html = '<div class="elementor-element elementor-widget elementor-widget-button">
    <div class="elementor-widget-container">
      <div class="elementor-button-wrapper">
        <a href="{siteurl}?path={vpn_link}" class="elementor-button-link elementor-button elementor-size-sm" role="button">
          <span class="elementor-button-content-wrapper">
            <span class="elementor-button-text">Open {carrier} Document Library</span>
          </span>
        </a>
      </div>
    </div>
  </div>';
  $search = ['{siteurl}','{vpn_link}','{carrier}'];
  $replace = [ home_url( '/tools/carrier-documents/' ), $vpn_link, get_the_title( $post->ID ) ];
  return str_replace( $search, $replace, $html );
}
add_shortcode( 'dirlisterbutton', __NAMESPACE__ . '\\dirlister_button' );