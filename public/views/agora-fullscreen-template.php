<?php $current_path = plugins_url('wp-agora-io') . '/public'; ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Agora.io Communication Chat</title>
  <?php wp_head() ?>
</head>
<body class="agora custom-background-image">
  <div class="agora-fullscreen-container controls-bottom window-mode gradient-4">

    <div class="main-video-screen" id="full-screen-video">
      <div id="video-canvas"></div>

      <div id="rejoin-container" class="rejoin-container" style="display: none">
        <button id="rejoin-btn" class="btn btn-primary btn-lg" type="button">
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <?php _e('Rejoin to this channel', 'agoraio'); ?>
        </button>
      </div>

      <div id="buttons-container" class="mt-3">
        <div class="control-btn">
          <button id="mic-btn" type="button" class="btn btn-block btn-dark btn-xs">
            <i id="mic-icon" class="fas fa-microphone"></i>
          </button>
        </div>
        <div class="control-btn main-btn">
          <button id="exit-btn"  type="button" class="btn btn-block btn-danger btn-xs">
            <i id="exit-icon" class="fas fa-phone-slash"></i>
          </button>
        </div>
        <div class="control-btn">
          <button id="video-btn"  type="button" class="btn btn-block btn-dark btn-xs">
            <i id="video-icon" class="fas fa-video"></i>
          </button>
        </div>
      </div>

    </div>

    <div class="audience-container" id="audience-avatars">
      <div class="avatar-circle local" id="local-stream-container">
        <div id="mute-overlay">
          <i id="mic-icon" class="fas fa-microphone-slash"></i>
        </div>
        <div id="no-local-video" class="text-center">
          <i id="user-icon" class="fas fa-user"></i>
        </div>
        <div id="local-video"></div>
      </div>

      <div class="remote-users">
        <div class="slick-avatars" id="slick-avatars">
          
        </div>
      </div>

    </div>
  </div>
  <?php wp_footer(); ?>
  <script>
    window.AGORA_UTILS = {
      agoraGenerateToken: agoraGenerateToken
    };
    // video profile settings
    window.cameraVideoProfile = '<?php echo $instance['videoprofile'] ?>'; // 640x480 @ 30fps & 750kbs
    window.screenVideoProfile = '<?php echo $instance['screenprofile'] ?>';
    window.addEventListener('load', function() {
      window.agoraAppId = '<?php echo $agora->settings['appId'] ?>'; // set app id
      window.channelName = '<?php echo $channel->title() ?>'; // set channel name
      window.channelId = '<?php echo $channel->id() ?>'; // set channel name
      window.userID = parseInt(`123${<?php echo $current_user->ID; ?>}`, 10);
      window.agoraMode = 'communication';
      if (window.userID===1230) {
        window.userID = 0;
      }

      window.AGORA_COMMUNICATION_UI.fullscreenInit();
    });


    // use tokens for added security
    function agoraGenerateToken() {
      return <?php
      $appID = $agora->settings['appId'];
      $appCertificate = $agora->settings['appCertificate'];
      $current_user = wp_get_current_user();

      if($appCertificate && strlen($appCertificate)>0) {
        $channelName = $channel->title();
        if ($current_user->ID !== 0) {
          $uid = '123'.$current_user->ID; // Get urrent user id
        } 

        // role should be based on the current user host...
        $settings = $channel->get_properties();
        $role = 'Role_Subscriber';
        $privilegeExpireTs = 0;
        if(!class_exists('RtcTokenBuilder')) {
          require_once(__DIR__.'/../../includes/token-server/RtcTokenBuilder.php');
        }
        echo '"'.AgoraRtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpireTs). '"';
      } else {
        echo 'null';
      }
      ?>;
    }
  </script>
</body>
</html>