<?php

namespace NCCAgent\wplogin;

function login_scripts(){
  wp_enqueue_style('ncc-login', plugin_dir_url( __FILE__ ) . '../' . NCC_CSS_DIR . '/login.css' );
}
add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\\login_scripts' );