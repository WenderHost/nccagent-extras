<?php

namespace NCCAgent\dirlister;

function dirlister( $atts ){
  wp_enqueue_script( 'dirlister' );
  global $post;
  $path = get_field( 'vpn_link', $post->ID );
  wp_localize_script( 'dirlister', 'wpvars', [ 'self' => get_permalink( $post ), 'path' => $path, 'endpoint' => rest_url('nccagent/v1/dirlister') ] );
  return '<div id="dirlister"><ul><li>DIRLISTER</li></ul></div>';
}
add_shortcode( 'dirlister', __NAMESPACE__ . '\\dirlister' );
