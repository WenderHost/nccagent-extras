<?php

namespace NCCAgent\csg;

/**
 * Endpoint setup for CSG Actuarial mobile app authentication.
 *
 * Given an email and password, WordPress will respond with a
 * success or fail if we have a user with `username=$email` and
 * `password=$password`.
 *
 * Route: {site_url}/wp-json/nccagent/v1/verfiyAccount
 *
 * REQUEST params:
 *
 * - email    (string) User's email address which is their `login` inside WordPress
 * - password (string) User's password
 */
function csg_api(){
  register_rest_route( 'nccagent/v1', 'verifyAccount', [
    'methods'   => 'POST,GET',
    'permission_callback' => function(){
      return true;
    },
    'args'      => [
      'email'     => [
        'validate_callback' => function( $param, $request, $key ){
          if( is_numeric( $param ) ){
            return true;
          } else {
            return is_email( $param );
          }
        }
      ],
      'password'  => [
        'validate_callback' => function( $param, $request, $key ){
          return ( empty( $param ) )? false : true ;
        }
      ]
    ],
    'callback'  => function( \WP_REST_Request $request ){
      $login = $request->get_param('email');
      $password = $request->get_param('password');

      if( empty( $login ) ){
        return new \WP_Error('noemail', 'You did not provide an email or NPN.', ['status' => 400]);
      }

      $user = get_user_by( 'login', $login );
      $user = ( is_email( $login ) )? get_user_by( 'email', $login ) : get_user_by( 'login', $login ) ;

      if( ! $user )
        return new \WP_Error( 'invalid_email', 'We could not locate a user with the NPN/email you provided (`' . $login . '`).', [
          'status' => 400,
        ]);

      if( $user && wp_check_password( $password, $user->data->user_pass, $user->ID ) ){
        $data = [ 'message' => 'Success', 'status' => 200 ];
        return new \WP_REST_Response( $data, 200 );
      } else {
        return new \WP_Error( 'invalid_password', 'Incorrect password.', [
          'status' => 400,
        ]);
      }
    },
  ]);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\csg_api' );