<?php 

// Shortcode [agora-broadcast]
function renderBroadcastShortcode($agora, $attrs) {

  // Avoid duplicated shortcode rendered
  if (WP_Agora_Public::isShortcodeRendered('[agora-broadcast]')) {
    return "<!-- Shortcode Already Rendered: ".print_r($agora, true)." -->";
  }

  $instance = $agora->getShortcodeAttrs('agora-broadcast', $attrs);

  if (($err = $agora->validateShortcode($instance))!==false) {
    return $err;
  }

  $agora->enqueueShortcodeStyles('broadcast');

  $channel = WP_Agora_Channel::get_instance($instance['channel_id']);
  if ($channel) {
    $props = $channel->get_properties();
    $current_user = wp_get_current_user();

    ob_start();
    $host = is_array($props['host']) ? $props['host'] : array($props['host']);
    if ( in_array($current_user->ID, $host) ) {
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
  } else {
    $out = '<!-- Agora: No channel found! -->';
  }

  return $out;
}