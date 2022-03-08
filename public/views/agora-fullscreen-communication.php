<?php
// $channelSettings    = $channel->get_properties();
// $videoSettings      = $channelSettings['settings'];
// $appearanceSettings = $channelSettings['appearance'];
// $recordingSettings  = $channelSettings['recording'];
// $current_user       = wp_get_current_user();
// $current_path       = plugins_url('wp-agora-io') . '/public';

// $remoteSpeakersPos = isset($agora->settings['agora-remote-speakers-position']) ? $agora->settings['agora-remote-speakers-position'] : '';

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Agora.io Communication Video</title>
  <?php wp_head() ?>
</head>
<body <?php body_class(); ?> style="min-height: 100vh; min-height: -webkit-fill-available;">
  <?php 
    $isFullScreenTemplate = true;
    require_once('agora-communication-templates-common.php'); 
  ?>
  <?php wp_footer(); ?>
</body>
</html>