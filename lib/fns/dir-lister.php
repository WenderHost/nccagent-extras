<?php

namespace NCCAgent\dirlister;

function dirlister( $atts ){
  wp_enqueue_script( 'dirlister' );
  global $post;

  if( get_query_var( 'path' ) ){
    $path = get_query_var( 'path' );
  } else {
    $path = get_field( 'vpn_link', $post->ID );
  }

  if( ! $path )
    $path = 'http://vpn.ncc-agent.com/docs/';

  wp_localize_script( 'dirlister', 'wpvars', [
    'self' => get_permalink( $post ),
    'path' => $path,
    'endpoint' => rest_url('nccagent/v1/dirlister'),
    'nonce' => wp_create_nonce( 'wp_rest' ) ]
  );
  return '<div id="dirlister"><h1>Carrier Document Library</h1><p>Using your browser\'s Back button will exit the document library. Use the links to navigate.</p><h5>...</h5><ul class="directory-listing"><li class="message">Loading directory...</li></ul></div>';
}
add_shortcode( 'dirlister', __NAMESPACE__ . '\\dirlister' );

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
  $replace = [ site_url( '/carrier-documents/' ), $vpn_link, get_the_title( $post->ID ) ];
  return str_replace( $search, $replace, $html );
}
add_shortcode( 'dirlisterbutton', __NAMESPACE__ . '\\dirlister_button' );