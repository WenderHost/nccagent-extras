<?php

namespace NCCAgent\wp_head;

function ie11_compat(){
  echo '<meta http-equiv="X-UA-Compatible" content="IE=edge;" />' . "\n";
}
add_action( 'wp_head', __NAMESPACE__ . '\\ie11_compat' );