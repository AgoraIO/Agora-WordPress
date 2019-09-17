<?php 

// Shortcode [agora-broadcast]
function renderBroadcastShortcode($agora, $instance) {

  // Avoid duplicated shortcode rendered
  if (WP_Agora_Public::isShortcodeRendered('[agora-broadcast]')) {
    return "<!-- Shortcode Already Rendered: ".print_r($instance, true)." -->";
  }

  $bootstrap_css = 'https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css';
  $fontawesome = 'https://use.fontawesome.com/releases/v5.7.0/css/all.css';
  wp_enqueue_style( 'bootstrap', $bootstrap_css, array(), null, 'all' );
  wp_enqueue_style( 'fontawesome', $fontawesome, array('bootstrap'), null, 'all' );
  

  $instance = shortcode_atts(
      array(
        'channel_id' => 0,
        'audio' => 'true',
        'video' => 'true',
        'screen' => 'false'
      ), $instance, 'agora-broadcast' );

  if(!$instance) { $instance = []; }

  if (((int)$instance['channel_id'])===0) {
    return '<p class="error">'.__('Please define the <b>channel_id</b> attribute to use on this shortcode', 'agoraio').'</div>';
  }


  if (!isset($agora->settings['appId'])) {
    return '<p class="error">'.__('Please configure your <b>Agora App ID</b> before use this shortcode', 'agoraio').'</div>';
  }

  $channel = WP_Agora_Channel::get_instance($instance['channel_id']);
  ob_start();
  require_once('views/wp-agora-io-broadcast.php');
  $out = ob_get_clean();

  WP_Agora_Public::addShortcodeRendered('[agora-broadcast]');
  return $out;
}