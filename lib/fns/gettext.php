<?php

namespace NCCAgent\gettext;

function translate_strings( $translated, $untranslated, $domain ) {

   if ( ! is_admin() ) {
      switch ( $translated ) {
         case 'Lost your password?' :
            $translated = 'Forgot password?';
            break;
      }
   }
   return $translated;
}
add_filter( 'gettext', __NAMESPACE__ . '\\translate_strings', 999, 3 );