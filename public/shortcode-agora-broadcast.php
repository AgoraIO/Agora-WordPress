<?php 

// Shortcode [agora-broadcast]
function renderBroadcastShortcode($agora, $attrs) {

  // Avoid duplicated shortcode rendered
  if (WP_Agora_Public::isShortcodeRendered('[agora-broadcast]')) {
    return "<!-- Shortcode Already Rendered: ".print_r($agora, true)." -->";
  }

  $bootstrap_css = 'https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css';
  $fontawesome = 'https://use.fontawesome.com/releases/v5.7.0/css/all.css';
  wp_enqueue_style( 'bootstrap', $bootstrap_css, array(), null, 'all' );
  wp_enqueue_style( 'fontawesome', $fontawesome, array('bootstrap'), null, 'all' );
  wp_enqueue_script( 'AgoraSDK', 'https://cdn.agora.io/sdk/web/AgoraRTCSDK-2.8.0.js', array('jquery'), null );

  $instance = shortcode_atts(
      array(
        'channel_id' => 0,
        'audio' => 'true',
        'video' => 'true',
        'screen' => 'false',
        'videoprofile' => '480p_9' // https://docs.agora.io/en/Video/API%20Reference/web/interfaces/agorartc.stream.html#setvideoprofile
      ), $attrs, 'agora-broadcast' );
  
  // TODO: Add validation here to avoid video and screen setting with the same value

  if(!$instance) { $instance = []; }

  if (((int)$instance['channel_id'])===0) {
    return '<p class="error">'.__('Please define the <b>channel_id</b> attribute to use on this shortcode', 'agoraio').'</div>';
  }


  if (!isset($agora->settings['appId'])) {
    return '<p class="error">'.__('Please configure your <b>Agora App ID</b> before use this shortcode', 'agoraio').'</div>';
  }
  require_once(__DIR__.'/../includes/token-server/RtcTokenBuilder.php');
  $channel = WP_Agora_Channel::get_instance($instance['channel_id']);
  $props = $channel->get_properties();
  $current_user = wp_get_current_user();

  ob_start();
  if ((int)$props['host']===$current_user->ID) {
    $agoraUserScript = 'js/agora-broadcast-client.js';
    require_once('views/wp-agora-io-broadcast.php');
  } else {
    // $agoraUserScript = 'js/agora-audience-client.js';
    require_once('views/wp-agora-io-audience.php');
  }
  $out = ob_get_clean();

  if (isset($agoraUserScript) && $agoraUserScript!=='') {
    wp_enqueue_script(
      'AgoraBroadcastClient',
      plugin_dir_url( __FILE__ ) . $agoraUserScript,
      array('AgoraSDK'), null );
  }

  WP_Agora_Public::addShortcodeRendered('[agora-broadcast]');
  return $out;
}