<?php

function ncc_quick_links(){
  global $post;
  $post_type = get_post_type( $post );

  $html = [];
  $links = [
    [
      'url'       => get_permalink( $post->ID ),
      'text'      => 'Back to <strong>' . get_the_title( $post->ID ) . ' Contracting &amp; Appointment</strong>',
      'post_type' => 'carrier',
    ],
    [
      'url'       => get_permalink( $post->ID ),
      'text'      => 'Back to <strong>' . get_the_title( $post->ID ) . ' Products and Carriers</strong>',
      'post_type' => 'product',
    ],
    [
      'url'       => 'contract-online',
      'text'      => 'Online Contracting for Medicare Agents',
    ],
    [
      'url'       => 'contracting/kit-request',
      'text'      => 'Request a Contracting Kit',
    ],
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
  }

  $links[] = [
      'url'  => 'plans',
      'text' => 'All Carriers &amp; Products'
  ];

  $html[] = '<h3>Quick Links</h3>';
  foreach( $links as $link ){
    if( isset( $link['post_type'] ) && ( $link['post_type'] != $post_type ) )
      continue;

    $url = ( ! ('http' == substr( $link['url'], 0, 4 ) ) )? site_url( $link['url'] ) : $link['url'] ;
    $list[] = '<a href="' . $url . '">' . $link['text'] . '</a>';
  }
  $html[] = '<ul><li>' . implode('</li><li>', $list ) . '</li></ul>';

  return implode( '', $html );
}