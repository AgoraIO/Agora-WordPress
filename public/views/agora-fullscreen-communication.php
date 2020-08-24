<?php
$current_path = plugins_url('wp-agora-io') . '/public';
$channelSettings    = $channel->get_properties();
$videoSettings      = $channelSettings['settings'];
$appearanceSettings = $channelSettings['appearance'];
$recordingSettings  = $channelSettings['recording'];
$current_user       = wp_get_current_user();
$current_path = plugins_url('wp-agora-io') . '/public';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Agora.io Communication Chat</title>
  <?php wp_head() ?>
</head>
<body <?php body_class(); ?>>
  <div class="agora agora-fullscreen">
    <section class="agora-container">
      <?php require_once "parts/header.php" ?>

      <div class="agora-content">
        <?php require_once "parts/header-controls.php" ?>

        <div id="screen-zone" class="screen">
          <div id="screen-users" class="screen-users screen-users-1">

            <div id="local-video" class="user"></div>
            
          </div>
        </div>
      </div>

      <?php require_once "parts/footer-communication.php" ?>
    </section>
  </div>
  <?php wp_footer(); ?>
  <script>
    // video profile settings
    window.cameraVideoProfile = '<?php echo $instance['videoprofile'] ?>'; // 640x480 @ 30fps & 750kbs
    window.screenVideoProfile = '<?php echo $instance['screenprofile'] ?>';
    window.agoraAppId = '<?php echo $agora->settings['appId'] ?>'; // set app id
    window.channelName = '<?php echo $channel->title() ?>'; // set channel name
    window.channelId = '<?php echo $channel->id() ?>'; // set channel name
    window.userID = parseInt(`${<?php echo $current_user->ID; ?>}`, 10);
    window.agoraMode = 'communication';

    window.addEventListener('load', function() {
      window.agoraLogLevel = window.location.href.indexOf('localhost')>0 ? AgoraRTC.Logger.ERROR : AgoraRTC.Logger.NONE;
      AgoraRTC.Logger.setLogLevel(AgoraRTC.Logger.ERROR);

      window.AGORA_COMMUNICATION_CLIENT.initClientAndJoinChannel(window.agoraAppId, window.channelName);
    });


    // use tokens for added security
    function agoraGenerateToken() {
      return <?php
      $appID = $agora->settings['appId'];
      $appCertificate = $agora->settings['appCertificate'];
      $current_user = wp_get_current_user();

      if($appCertificate && strlen($appCertificate)>0) {
        $channelName = $channel->title();
        $uid = $current_user->ID; // Get urrent user id

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
    
    window.AGORA_TOKEN_UTILS = {
      agoraGenerateToken: agoraGenerateToken
    };
  </script>
</body>
</html>