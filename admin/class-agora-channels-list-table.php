<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 *
 * @link       https://www.agora.io
 * @since      1.0.0
 *
 * @package    WP_Agora
 * @subpackage WP_Agora/admin
 */
class Agora_Channels_List_Table extends WP_List_Table {

  public function __construct() {
    parent::__construct( array(
      'singular' => 'channel',
      'plural' => 'channels',
      'ajax' => false,
    ) );
  }

  function get_columns() {
    // return get_column_headers( get_current_screen() );
    $columns = [
      'cb'      => '<input type="checkbox" />',
      'name'    => __( 'Channel Name', 'agoraio' ),
      'type' => __( 'Channel type', 'agoraio' ),
      'Shortcode'    => __( 'Shortcode', 'agoraio' )
    ];

    return $columns;
  }
  
  public function prepare_items() {
    $this->_column_headers = $this->get_column_info();

    /** Process bulk action */
    $this->process_bulk_action();

    $per_page     = $this->get_items_per_page( 'agoraio_channels_per_page', 5 );
    $current_page = $this->get_pagenum();
    $total_items  = self::record_count();

    $this->set_pagination_args( [
      'total_items' => $total_items, //WE have to calculate the total number of items
      'per_page'    => $per_page //WE have to determine how many items to show on a page
    ] );


    $this->items = self::get_channels( $per_page, $current_page );
  }

  public function process_bulk_action() {

    //Detect when a bulk action is being triggered...
    if ( 'delete' === $this->current_action() ) {

      // In our file that handles the request, verify the nonce.
      $nonce = esc_attr( $_REQUEST['_wpnonce'] );

      if ( ! wp_verify_nonce( $nonce, 'sp_delete_channel' ) ) {
        die( 'Go get a life script kiddies' );
      }
      else {
        self::delete_channel( absint( $_GET['channel'] ) );

        wp_redirect( esc_url( add_query_arg() ) );
        exit;
      }

    }

    // If the delete bulk action is triggered
    if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
         || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
    ) {

      $delete_ids = esc_sql( $_POST['bulk-delete'] );

      // loop over the array of record IDs and delete them
      foreach ( $delete_ids as $id ) {
        self::delete_channel( $id );

      }

      wp_redirect( esc_url( add_query_arg() ) );
      exit;
    }
  }

  /** Text displayed when no channel data is available */
  public function no_items() {
    _e( 'No agora channels avaliable.', 'agoraio' );
  }


  public static function get_channels( $per_page = 5, $page_number = 1 ) {
    return WP_Agora_Channel::find(array(
      'order' => !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'ASC',
      'orderby' => !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '',
      'posts_per_page' => $per_page,
      'offset' => $page_number-1
    ));
  }

  public static function record_count() {
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}channels";

    return $wpdb->get_var( $sql );
  }


  function column_name( $item ) {

    // create a nonce
    $delete_nonce = wp_create_nonce( 'sp_delete_channel' );

    $title = '<strong>' . $item['name'] . '</strong>';

    $actions = [
      'delete' => sprintf( '<a href="?page=%s&action=%s&channel=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
    ];

    return $title . $this->row_actions( $actions );
  }

  /**
   * Render a column when no column specific method exists.
   *
   * @param array $item
   * @param string $column_name
   *
   * @return mixed
   */
  public function column_default( $item, $column_name ) {
    switch ( $column_name ) {
      case 'address':
      case 'city':
        return $item[ $column_name ];
      default:
        return print_r( $item, true ); //Show the whole array for troubleshooting purposes
    }
  }
}