<?php

class WP_Agora_Channel {

  const post_type = 'agora_channel';

  private static $found_items = 0;
  private static $current = null;
  private static $defaultVideoSettings = array(
    'external-rtmpServerURL' => '',
    'external-streamKey' => '',
    'external-width' => 640,
    'external-height' => 360,
    'external-videoBitrate' => 400,
    'external-videoFramerate' => 15,
    'external-lowLatency' => false,
    'external-audioSampleRate' => 48000,
    'external-audioBitrate' => 48,
    'external-audioChannels' => 1,
    'external-videoGop' => 30,
    'external-videoCodecProfile' => 100,
    'external-backgroundColor' => '#efefef',
    'inject-width' => 640,
    'inject-height' => 360,
    'inject-videoBitrate' => 400,
    'inject-videoFramerate' => 15,
    'inject-lowLatency' => false,
    'inject-audioSampleRate' => 48000,
    'inject-audioBitrate' => 48,
    'inject-audioChannels' => 1,
    'inject-videoGop' => 30,
    'inject-videoCodecProfile' => 100,
    'inject-backgroundColor' => '#efefef',
  );
  private static $defaultAppearanceSettings = array(
    'splashImageURL' => '',
    'noHostImageURL' => '',
    'watchButtonText' => 'Watch the Live Stream',
    'watchButtonIcon' => true,
    'activeButtonColor' => '#343a40',
    'disabledButtonColor' => '#dc3545',
  );

  // https://docs.agora.io/en/cloud-recording/cloud_recording_api_rest?platform=All%20Platforms#parameters-1
  private static $defaultRecordingSettings = array(
    'vendor' => '',
    'region' => 0,
    'bucket' => '',
    'accessKey' => '',
    'secretKey' => ''
  );

  // private channel attrs
  private $id;
  private $name;
  private $title;
  private $locale;
  private $properties = array();
  // private $videoSettings = array();
  // private $appearanceSettings = array();

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

    // $properties = $channel->get_properties();

    // foreach ( $properties as $key => $value ) {
      // $properties[$key] = WPCF7_ContactFormTemplate::get_default( $key );
    // }

    // $channel->properties = $properties;

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

      // $properties = $this->get_properties();
      $videoSettings = get_post_meta( $this->id, 'channel_video_settings', true );
      $appearanceSettings = get_post_meta( $this->id, 'channel_appearance_settings', true );
      $recordingSettings = get_post_meta( $this->id, 'channel_recording_settings', true );
      $channelType = get_post_meta( $this->id, 'channel_type', true );
      $channelUserHost = get_post_meta( $this->id, 'channel_user_host', true );

      $this->properties = array(
        'type' => $channelType,
        'host' => $channelUserHost,
        'settings' => $videoSettings,
        'appearance' => $appearanceSettings,
        'recording' => $recordingSettings
      );
      
      // $this->upgrade();
    }

    do_action( 'agoraio_channel', $this );
  }

  public function get_properties() {
    $properties = (array) $this->properties;
    $properties = wp_parse_args( $properties, array(
      'type' => '',
      'host' => array(),
      'settings' => array_merge(self::$defaultVideoSettings),
      'appearance' => array_merge(self::$defaultAppearanceSettings),
      'recording' => array_merge(self::$defaultRecordingSettings)
    ) );
    $properties = (array) apply_filters( 'agoraio_channel_properties', $properties, $this );
    return $properties;
  }

  public function save( $args ) {
    if ( $args['post_ID']==='-1' ) {
      // echo "<pre>".print_r('insert_post', true)."</pre>";
      $post_id = wp_insert_post( array(
        'post_type' => self::post_type,
        'post_status' => 'publish',
        'post_title' => sanitize_text_field($args['post_title']),
      ) );
    } else {
      $post_id = wp_update_post( array(
        'ID' => (int) $args['post_ID'],
        'post_status' => 'publish',
        'post_title' => sanitize_text_field($args['post_title']),
      ) );
    }

    $videoSettings = array();
    array_map(function($key) use ($args, &$videoSettings) {
      $videoSettings[$key] = sanitize_text_field($args[$key]);
      return $videoSettings[$key];
    }, array_keys(self::$defaultVideoSettings));

    $appearanceSettings = array();
    array_map(function($key) use ($args, &$appearanceSettings) {
      if ($key==='splashImageURL' || $key==='noHostImageURL') {
        $value = esc_url_raw($args[$key]);
      } else if ($key==='watchButtonText') {
        $value = sanitize_text_field($args[$key]);
      } else {
        $value = sanitize_text_field($args[$key]);
      }
      $appearanceSettings[$key] = $value;
      return $value;
    }, array_keys(self::$defaultAppearanceSettings));

    $recordingSettings = array();
    array_map(function($key) use ($args, &$recordingSettings) {
      $recordingSettings[$key] = sanitize_text_field($args[$key]);
      return $recordingSettings[$key];
    }, array_keys(self::$defaultRecordingSettings));

    update_post_meta($post_id, 'channel_video_settings', $videoSettings);
    update_post_meta($post_id, 'channel_appearance_settings', $appearanceSettings);
    update_post_meta($post_id, 'channel_recording_settings', $recordingSettings);
    update_post_meta($post_id, 'channel_type', sanitize_key($args['type']));

    if (isset($args['host'])) {
      if (is_array($args['host'])) {
        $hosts = array_map('sanitize_key', $args['host']);
      } else {
        $hosts = sanitize_key($args['host']);
      }
      update_post_meta($post_id, 'channel_user_host', $hosts);
    }

    unset($args['_wp_http_referer']);
    unset($args['agoraio-locale']);
    unset($args['agoraio-save']);
    unset($args['active-tab']);
    unset($args['_wpnonce']);
    unset($args['action']);
    unset($args['post']);
    unset($args['page']);

    $args['id'] = $post_id;

    do_action( 'agoraio_after_save', $args );

    return $args;
  }

  public function delete() {
    if ( $this->initial() ) {
      resturn;
    }

    if ( wp_delete_post( $this->id, true ) ) {
      $this->id = 0;
      return true;
    }

    return false;
  }

  public function initial() {
    return empty( $this->id );
  }

  public function id() {
    return $this->id;
  }

  public function title() {
    return $this->title;
  }

  public function type() {
    return ucfirst( $this->properties['type'] );
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

  public function shortcode() {
    $type = $this->properties['type'];
    if($type==='broadcast') {
      return '[agora-broadcast channel_id="'.$this->id.'"]';
    } else if($type==='communication') {
      return '[agora-communication channel_id="'.$this->id.'"]';
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
