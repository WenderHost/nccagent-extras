<?php

function ncc_quick_links(){
  global $post;
  $post_type = get_post_type( $post );
  $carrierproduct = sanitize_title_with_dashes( get_query_var( 'carrierproduct' ) );

  $html = [];
  $links = [];

  if( ! empty( $carrierproduct ) ){
    $links[] = [
      'url'       => get_permalink( $post->ID ),
      'text'      => 'All ' . get_the_title( $post->ID ) . ' Products</strong>',
      'post_type' => 'carrier',
    ];
  }

  $links[] = [
      'url'       => get_permalink( $post->ID ),
      'text'      => 'Back to <strong>' . get_the_title( $post->ID ) . ' Products and Carriers</strong>',
      'post_type' => 'product',
    ];
  $links[] = [
      'url'       => 'contracting/contract-online',
      'text'      => 'Contract with ' . get_the_title( $post->ID ) . ' Online',
    ];
  $links[] = [
      'url'       => 'contracting/kit-request',
      'text'      => 'Request a Product Kit',
    ];

  if( 'carrier' == $post_type ){
    $agent_black_book_url = get_field('agent_black_book');
    if( $agent_black_book_url )
      $links[] = [
        'url'  => $agent_black_book_url,
        'text' => get_the_title( $post->ID ) . ' Agent Black Book',
      ];

    $ahip_certification_url = get_field('ahip_certification');
    if( $ahip_certification_url )
      $links[] = [
        'url'  => $ahip_certification_url,
        'text' => get_the_title( $post->ID ) . ' AHIP Certification',
      ];
    $vpn_link = get_field( 'vpn_link');
    if( $vpn_link )
      $links[] = [
        'url'   => site_url('carrier-documents/'). '?path=' . $vpn_link,
        'text'  => get_the_title( $post->ID ) . ' Documents',
      ];
  }

  if( ! empty( $carrierproduct ) ){
    $links[] = [
        'url'  => 'plans',
        'text' => 'All Carriers &amp; Products'
    ];
  }

  $html[] = '<h2 style="margin-top: 1em;">Quick Links</h2>';
  foreach( $links as $link ){
    if( isset( $link['post_type'] ) && ( $link['post_type'] != $post_type ) )
      continue;

    $url = ( ! ('http' == substr( $link['url'], 0, 4 ) ) )? site_url( $link['url'] ) : $link['url'] ;
    $list[] = '<a href="' . $url . '">' . $link['text'] . '</a>';
  }
  $html[] = '<ul><li>' . implode('</li><li>', $list ) . '</li></ul>';

  return implode( '', $html );
}