<?php

class WP_Agora_Channel {

  const post_type = 'agora_channel';

  private static $found_items = 0;
  private static $current = null;

  // private channel attrs
  private $id;
  private $name;
  private $title;
  private $locale;
  private $properties = array();


  public static function init_agoraio() {
    register_post_type( self::post_type, array(
      'labels' => array(
        'name' => __( 'Channels', 'agoraio' ),
        'singular_name' => __( 'Channel', 'agoraio' ),
      ),
      'rewrite' => false,
      'query_var' => false,
      'public' => false,
      'capability_type' => 'page',
    ));

    // Just in case, in the future I need to do something after register post type
    do_action( 'agora_init' );
  }

  public static function count() {
    return self::$found_items;
  }

  public static function get_current() {
    return self::$current;
  }

  public static function find( $args = '' ) {
    $defaults = array(
      'post_status' => 'any',
      'posts_per_page' => -1,
      'offset' => 0,
      'orderby' => 'ID',
      'order' => 'ASC',
    );

    $args = wp_parse_args( $args, $defaults );

    $args['post_type'] = self::post_type;

    $q = new WP_Query();
    $posts = $q->query( $args );

    self::$found_items = $q->found_posts;

    $objs = array();

    foreach ( (array) $posts as $post ) {
      $objs[] = new self( $post );
    }

    return $objs;
  }


  public static function get_template( $args = '' ) {
    global $l10n;

    $defaults = array( 'locale' => null, 'title' => '' );
    $args = wp_parse_args( $args, $defaults );

    $locale = $args['locale'];
    $title = $args['title'];

    if ( $locale ) {
      $mo_orig = $l10n['agoraio'];
      wpcf7_load_textdomain( $locale );
    }

    self::$current = $channel = new self;
    $channel->title = ( $title ? $title : __( 'Untitled', 'agoraio' ) );
    $channel->locale = ( $locale ? $locale : get_user_locale() );

    $properties = $channel->get_properties();

    foreach ( $properties as $key => $value ) {
      // $properties[$key] = WPCF7_ContactFormTemplate::get_default( $key );
    }

    $channel->properties = $properties;

    $channel = apply_filters( 'agoraio_default_pack', $channel, $args );

    if ( isset( $mo_orig ) ) {
      $l10n['agoraio'] = $mo_orig;
    }

    return $channel;
  }

  public static function get_instance( $post ) {
    $post = get_post( $post );

    if ( !$post || self::post_type !== get_post_type( $post ) ) {
      return false;
    }

    return self::$current = new self( $post );
  }

  private function __construct( $post = null ) {
    $post = get_post( $post );

    if ( $post && self::post_type == get_post_type( $post ) ) {
      $this->id = $post->ID;
      $this->name = $post->post_name;
      $this->title = $post->post_title;
      $this->locale = get_post_meta( $post->ID, '_locale', true );

      $properties = $this->get_properties();

      foreach ( $properties as $key => $value ) {
        if ( metadata_exists( 'post', $post->ID, '_' . $key ) ) {
          $properties[$key] = get_post_meta( $post->ID, '_' . $key, true );
        } elseif ( metadata_exists( 'post', $post->ID, $key ) ) {
          $properties[$key] = get_post_meta( $post->ID, $key, true );
        }
      }

      $this->properties = $properties;
      // $this->upgrade();
    }

    do_action( 'agoraio_channel', $this );
  }

  public function get_properties() {
    $properties = (array) $this->properties;

    $properties = wp_parse_args( $properties, array(
      'type' => '',
      'host' => array(),
      'settings' => array(
        'width' => 640,
        'height' => 360,
        'videoBitrate' => 400,
        'videoFramerate' => 15,
        'lowLatency' => false,
        'audioSampleRate' => 48000,
        'audioBitrate' => 48,
        'audioChannels' => 1,
        'videoGop' => 30,
        'videoCodecProfile' => 100,
        'backgroundColor' => '#efefef',
      ),
      'appearance' => array(
        'splashImageURL' => '',
        'noHostImageURL' => '',
      ),
    ) );

    $properties = (array) apply_filters( 'agoraio_channel_properties',
      $properties, $this );

    return $properties;
  }

  public function set_properties( $properties ) {
    $defaults = $this->get_properties();

    $properties = wp_parse_args( $properties, $defaults );
    $properties = array_intersect_key( $properties, $defaults );

    $this->properties = $properties;
  }

  public function initial() {
    return empty( $this->id );
  }

  public function title() {
    return $this->title;
  }

  public function set_title( $title ) {
    $title = strip_tags( $title );
    $title = trim( $title );

    if ( '' === $title ) {
      $title = __( 'Untitled', 'contact-form-7' );
    }

    $this->title = $title;
  }

  public function locale() {
    if ( agoraio_is_valid_locale( $this->locale ) ) {
      return $this->locale;
    } else {
      return '';
    }
  }

  public function set_locale( $locale ) {
    $locale = trim( $locale );

    if ( agoraio_is_valid_locale( $locale ) ) {
      $this->locale = $locale;
    } else {
      $this->locale = 'en_US';
    }
  }

  public function shortcode_attr( $name ) {
    if ( isset( $this->shortcode_atts[$name] ) ) {
      return (string) $this->shortcode_atts[$name];
    }
  }

}


function agoraio_is_valid_locale($locale) {
  $pattern = '/^[a-z]{2,3}(?:_[a-zA-Z_]{2,})?$/';
  return (bool) preg_match( $pattern, $locale );
}
