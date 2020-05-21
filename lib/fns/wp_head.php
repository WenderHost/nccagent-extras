<?php

namespace NCCAgent\wp_head;

function ie11_compat(){
  echo '<meta http-equiv="X-UA-Compatible" content="IE=edge;" />' . "\n";
}
add_action( 'wp_head', __NAMESPACE__ . '\\ie11_compat' );

function remove_mobile_tel_links(){
  echo '<meta name="format-detection" content="telephone=no">' . "\n";
}
add_action( 'wp_head', __NAMESPACE__ . '\\remove_mobile_tel_links' );