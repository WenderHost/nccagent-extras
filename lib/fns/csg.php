<?php

namespace NCCAgent\csg;

function csg_api(){
  register_rest_route( 'nccagent/v1', 'verifyAccount', [
    'methods'   => 'POST,GET',
    'args'      => [
      'email'     => [
        'validate_callback' => function( $param, $request, $key ){
          return is_email( $param );
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
        return new \WP_Error('noemail', 'You did not provide an email.', ['status' => 400]);
      }

      $user = get_user_by( 'login', $login );

      if( ! $user )
        return new \WP_Error( 'invalid_email', 'We could not locate a user with the login/email you provided (`' . $login . '`).', [
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