<?php
$channelSettings    = $channel->get_properties();
$videoSettings      = $channelSettings['settings'];
$appearanceSettings = $channelSettings['appearance'];
$recordingSettings  = $channelSettings['recording'];
$current_user       = wp_get_current_user();
$current_path       = plugins_url('wp-agora-io') . '/public';
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
  <div id="agora-root" class="agora agora-fullscreen">
    <section class="agora-container">
      <?php require_once "parts/header.php" ?>

      <div class="agora-content">
        <?php require_once "parts/header-controls.php" ?>

        <div id="screen-zone" class="screen">
          <div id="screen-users" class="screen-users screen-users-1">

            <div id="local-video" class="user">
              <div id="mute-overlay" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div>
              <div id="no-local-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div>
            </div>
            
          </div>
        </div>
      </div>

      <?php require_once "parts/footer-communication.php" ?>
    </section>
  </div>
  <?php wp_footer(); ?>
  <?php require_once "parts/scripts-common.php" ?>
  <script>
    window.agoraMode = 'communication';

    window.addEventListener('load', function() {
      AgoraRTC.Logger.enableLogUpload();
      AgoraRTC.Logger.setLogLevel(AgoraRTC.Logger.ERROR);

      window.AGORA_COMMUNICATION_CLIENT.initClientAndJoinChannel(window.agoraAppId, window.channelName);
    });
  </script>
</body>
</html>