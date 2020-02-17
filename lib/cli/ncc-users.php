<?php

/**
 * NCC User importer.
 */
class NCC_Users_CLI  extends WP_CLI_Command{
  public function __construct(){

  }

  /**
   * Imports NCC Users from a CSV.
   *
   * The Users CSV should have the following columns:
   *
   * - First Name
   * - Last Name
   * - Email
   * - NPN
   * - Marketer Email
   *
  * ## OPTIONS
   *
   * --filename=<csv_filename>
   * : Specify the name of the CSV you are importing.
   *
   * @param      <type>  $args        The arguments
   * @param      <type>  $assoc_args  The associated arguments
   */
  public function import( $args, $assoc_args ){

    if( ! isset( $assoc_args['filename'] ) || empty( $assoc_args['filename'] ) )
      WP_CLI::error('Please provide a filename as the first positional argument to this script.');
    $filename = $assoc_args['filename'];

    if( ! file_exists( $filename ) )
      WP_CLI::error('File `' . $filename . '` not found.');

    $total_lines = count( file( $filename ) );
    $row = 1;
    $errors = [];
    $emails = [];
    $created_users = 0;
    $existing_users = 0;
    if( ( $handle = fopen( $filename, 'r' ) ) !== false ){
      $progress = \WP_CLI\Utils\make_progress_bar( '🔔 Importing Users', $total_lines );
      while( ( $data = fgetcsv( $handle, 2048 ) ) !== false ){
        $num = count( $data );

        if( 1 == $row ){
          $x = 0;
          foreach( $data as $heading ){
            $row_heading_keys[$heading] = $x;
            $x++;
          }
          $row++;
          continue;
        }

        $firstname = $data[ $row_heading_keys['First Name'] ];
        $lastname = $data[ $row_heading_keys['Last Name'] ];
        $email = strtolower( $data[ $row_heading_keys['Email'] ] );
        $npn = $data[ $row_heading_keys['NPN'] ];
        $marketer = $data[ $row_heading_keys['Marketer Email'] ];
        if( ! is_email( $email ) ){
          $errors[] = [
            'type'    => 'invalidemail',
            'message' => 'Row ' . $row . ': Invalid email for ' . $firstname . ' ' . $lastname . ' (`' . $email . '`).',
          ];
          continue;
        }
        if( ! array_search( $email, $emails ) ){
          $users[] = [
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $email,
            'npn'       => $npn,
            'marketer'  => $marketer,
          ];
        } else {
          $errors[] = [
            'type'    => 'duplicateemail',
            'message' => 'Row ' . $row . ': Duplicate email (`' . $email . '`)',
          ];
        }
        $row++;
      }

      // Create the Users
      foreach( $users as $user_key => $user ){
        if( username_exists( $user['email'] ) || email_exists( $user['email'] ) ){
          $users[$user_key]['✅'] = '⛔︎';
          $existing_users++;
          continue;
        }

        $user_id = wp_insert_user([
          'user_pass' => wp_generate_password( 12 ),
          'user_login' => $user['email'],
          'display_name' => $user['firstname'],
          'first_name' => $user['firstname'],
          'last_name' => $user['lastname'],
          'role' => 'subscriber',
        ]);
        if( $user_id ){
          wp_update_user([
            'ID' => $user_id,
            'user_email' => $user['email'],
          ]);

          if( isset( $npn ) && ! empty( $npn ) )
            add_user_meta( $user_id, 'npn', $npn, true );

          $marketer_id = $this->_get_marketer_id( $user['marketer'] );
          if( $marketer_id )
            add_user_meta( $user_id, 'marketer', $marketer_id );
        }
        $created_users++;

        //if( 1 == $created_users )
        //  WP_CLI::line("\n" . str_repeat('-', 20 ) . ' NEW USERS ' . str_repeat('-', 20 ) );
        $users[$user_key]['✅'] = ( $user_id )? '✅' : '⛔︎' ;
          //WP_CLI::success( 'Created user: ' . $user['firstname'] . ' ' . $user['lastname'] . ' (' . $user['email'] . ').' );
        $progress->tick();
      }
      fclose( $handle );
      $progress->finish();

      // Display the users in a table:
      WP_CLI\Utils\format_items( 'table', $users, ['firstname','lastname','email','npn','marketer','✅'] );
      \WP_CLI::line( "Key:\n✅ User created\n⛔︎ User already exists\n----\n" );

      WP_CLI::line("\n" . str_repeat('-', 20 ) . ' FILE STATS ' . str_repeat('-', 20 ) );
      WP_CLI::success('Opened `' . basename( $filename ) . '` with ' . $row . ' lines.');
      WP_CLI::line('👉 ' . $existing_users . ' users already existed.');
      if( 0 < count( $errors ) ){
        WP_CLI::line("\n" . str_repeat('-', 20 ) . ' ERRORS ' . str_repeat('-', 20 ) . "\n" . 'I found the following errors:');
        foreach( $errors as $error ){
          WP_CLI::line('🚨 ' . $error['message'] );
        }
      }

    } else {
      WP_CLI::error('I could not open the file you supplied. Please check the file\'s permissions.');
    }

  }

  private function _get_marketer_id( $email = '' ){
    if( empty( $email ) || ! is_email( $email ) )
      return false;

    $marketers = get_posts([
      'post_type'   => 'team_member',
      'numberposts' => -1,
      'meta_key'    => 'email',
      'meta_value'  => $email,
    ]);
    if( ! $marketers )
      return false;

    return $marketers[0]->ID;
  }
}
$nccUsersCLI = new NCC_Users_CLI();
if( class_exists( '\\WP_CLI' ) )
  \WP_CLI::add_command( 'ncc users', $nccUsersCLI );
