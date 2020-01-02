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

  wp_localize_script( 'dirlister', 'wpvars', [ 'self' => get_permalink( $post ), 'path' => $path, 'endpoint' => rest_url('nccagent/v1/dirlister') ] );
  return '<div id="dirlister"><h1>Document Resources</h1><h5>...</h5><ul><li>Loading directory...</li></ul></div>';
}
add_shortcode( 'dirlister', __NAMESPACE__ . '\\dirlister' );

function dirlister_button( $atts ){
  global $post;
  $vpn_link = get_field( 'vpn_link', $post->ID );

  if( empty( $vpn_link ) )
    return '<p><code>ERROR: No`vpn_link` found.</code></p>';

  $html = '<div class="elementor-element elementor-widget elementor-widget-button">
    <div class="elementor-widget-container">
      <div class="elementor-button-wrapper">
        <a href="{siteurl}?path={vpn_link}" target="_blank" class="elementor-button-link elementor-button elementor-size-sm" role="button">
          <span class="elementor-button-content-wrapper">
            <span class="elementor-button-icon elementor-align-icon-right"><i aria-hidden="true" class="far fa-window-restore"></i></span>
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