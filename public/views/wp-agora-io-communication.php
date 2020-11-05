<?php
$channelSettings    = $channel->get_properties();
$videoSettings      = $channelSettings['settings'];
$appearanceSettings = $channelSettings['appearance'];
$current_user       = wp_get_current_user();
?>
<div id="agora-root" class="agora agora-communication">
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

  <?php require_once "parts/scripts-common.php" ?>
  <script>
    window.agoraMode = 'communication';

    window.addEventListener('load', function() {
      // window.agoraLogLevel = window.location.href.indexOf('localhost')>=0 ?  : AgoraRTC.Logger.NONE;
      AgoraRTC.Logger.enableLogUpload();
      AgoraRTC.Logger.setLogLevel(AgoraRTC.Logger.ERROR);
      window.AGORA_COMMUNICATION_CLIENT.initClientAndJoinChannel(window.agoraAppId, window.channelName);
    });
  </script>
</div>