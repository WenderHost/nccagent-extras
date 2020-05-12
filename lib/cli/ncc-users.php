<?php

/**
 * NCC User importer.
 */
class NCC_Users_CLI  extends WP_CLI_Command{
  public function __construct(){
    $this->data_keys = [];
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

        // Store column headings in $row_heading_keys with each
        // column name as the array key and the corresponding
        // $data[$key] as the value. So, calling something
        // like $row_heading_keys['email'] will give us the
        // array key to use with $data to return that value.
        if( 1 == $row ){
          foreach( $data as $key => $column ){
            $column_name = $this->_format_column_name( $column );
            $this->data_keys[$column_name] = $key;
          }
          $row++;
          \WP_CLI::line('🔔 We are importing with the following columns: ' . implode( ', ', $data ) );
          continue;
        }

        // Process the row
        $mapped_data = $this->_map_row_values( $data );

        $firstname = $mapped_data['firstname'];
        $lastname = $mapped_data['lastname'];
        $email = $mapped_data['email'];
        if( empty( $email ) )
          $email = $mapped_data['email_2'];
        $npn = $mapped_data['npn'];
        $marketer_id = $mapped_data['marketer_id'];
        if( ! is_email( $email ) ){
          $errors[] = [
            'type'    => 'invalidemail',
            'message' => 'Row ' . $row . ': Invalid email for ' . $firstname . ' ' . $lastname . ' (`' . $email . '`).',
          ];
          continue;
        }
        if( ! array_search( $email, $emails ) ){
          $users[] = [
            'firstname'    => $firstname,
            'lastname'     => $lastname,
            'email'        => $email,
            'npn'          => $npn,
            'marketer'     => get_the_title( $marketer_id ),
            'marketer_id'  => $marketer_id,
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
      // 05/11/2020 (12:03) - TODO: Need to handle pre-existing users
      // and update them.
      foreach( $users as $user_key => $user ){
        if( username_exists( $user['npn'] ) || email_exists( $user['email'] ) ){
          $users[$user_key]['✅'] = '⛔︎';
          $existing_users++;
          continue;
        }

        $user_id = wp_insert_user([
          'user_pass' => wp_generate_password( 12 ),
          'user_login' => $user['npn'],
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
            add_user_meta( $user_id, 'npn', $user['npn'], true );

          if( $user['marketer_id'] && is_numeric( $user['marketer_id'] ) )
            add_user_meta( $user_id, 'marketer_id', $user['marketer_id'] );

          // Add additional user meta
          foreach( $this->data_keys as $key => $id ){
            switch( $key ){
              case 'npn':
              case 'firstname':
              case 'lastname':
              case 'primary_pal':
              case 'marketer_id':
              case 'marketer':
              case 'email':
              case 'email_2':
                // nothing
                break;

              default:
                add_user_meta( $user_id, $key, $mapped_data[$key] );
            }
          }

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

  /**
   * Formats a column heading.
   *
   * Formats a column heading according to these specs:
   *
   * - Trims whitespace before and after
   * - All lower case
   * - Replaces interior spaces with underscores
   *
   * Example: `Source File Name` becomes `source_file_name`.
   *
   * @param      string  $name   The column name
   *
   * @return     string  The formatted column name
   */
  private function _format_column_name( $name = '' ){
    if( empty( $name ) )
      \WP_CLI::error( '🚨 _format_column_name() received an empty input. Halting...' );

    $formatted_name = trim( $name );
    $formatted_name = strtolower( $formatted_name );
    $formatted_name = str_replace(' ', '_', $formatted_name );
    $formatted_name = str_replace('_-_', '_', $formatted_name );
    return $formatted_name;
  }

  /**
   * Maps a row of data to an associative array.
   *
   * Maps a row of CSV data to an associative array with the
   * array keys corresponding to the column heading.
   *
   * @param      array          $data   The row data
   *
   * @return     array|boolean  The row data mapped to an associative array.
   */
  private function _map_row_values( $data = [] ){
    if( 0 == count( $data ) )
      return false;

    $row_values = [];
    $data_keys = $this->data_keys;
    foreach ( $data_keys as $name => $key ) {
      switch( $name ){
        case 'e-mail':
        case 'email':
          $row_values['email'] = strtolower( $data[$key] );
          break;

        case 'e-mail_2':
        case 'email_2':
          $row_values['email_2'] = strtolower( $data[$key] );
          break;

        case 'first_name':
        case 'firstname':
          $row_values['firstname'] = $data[$key];
          break;

        case 'last_name':
        case 'lastname':
          $row_values['lastname'] = $data[$key];
          break;

        case 'marketer_email':
          $row_values['marketer_email'] = $data[$key];
          if( ! isset( $row_values['marketer_id'] ) )
            $row_values['marketer_id'] = $this->_get_marketer_id( $data[$key], 'email' );
          break;

        case 'primary_pal':
          $search = ['deeanne','DeeAnne'];
          $replace = ['Dee Anne','Dee Anne'];
          $primary_pal = str_replace( $search, $replace, $data[$key] );

          $row_values['primary_pal'] = $primary_pal;
          if( ! isset( $row_values['marketer_id'] ) )
            $row_values['marketer_id'] = $this->_get_marketer_id( $primary_pal, 'name' );
          break;

        default:
          $row_values[$name] = $data[$key];
      }

    }

    return $row_values;
  }

  private function _get_marketer_id( $key, $by = 'email' ){
    if( 'email' == $by && ( empty( $key ) || ! is_email( $key ) ) )
      return false;

    if( 'email' == $by ){
      $marketers = get_posts([
        'post_type'   => 'team_member',
        'numberposts' => -1,
        'meta_key'    => 'email',
        'meta_value'  => $email,
      ]);
    } else {
      global $wpdb;
      $like = '%' . $wpdb->esc_like( $key ) . '%';
      $post_id = $wpdb->get_var( $wpdb->prepare('SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title LIKE %s AND post_type=%s', $like, 'team_member' ) );
      if( $post_id &&  is_numeric( $post_id ) )
        return $post_id;
    }
    if( ! $marketers )
      return false;

    return $marketers[0]->ID;
  }
}
$nccUsersCLI = new NCC_Users_CLI();
if( class_exists( '\\WP_CLI' ) )
  \WP_CLI::add_command( 'ncc users', $nccUsersCLI );
