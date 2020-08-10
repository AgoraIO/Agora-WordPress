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

        <div class="screen">
          <div id="screen-users" class="screen-users screen-users-1">

            <div id="local-video" class="user"></div>

            <!-- <div id="local-video" class="user"><div id="player_1" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video1" style="width: 100%; height: 100%; position: absolute; transform: rotateY(180deg); object-fit: cover;" autoplay="" muted="" playsinline=""></video><audio id="audio1" autoplay="" muted="" playsinline=""></audio></div></div>

            <div id="2327922690_container" class="user remote-stream-container"><div id="2327922690_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2327922690_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2327922690" class="remote-video"><div id="player_2327922690" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2327922690" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" playsinline=""></video><audio id="audio2327922690" autoplay="" playsinline=""></audio></div></div></div>

            <div id="2327922691_container" class="user remote-stream-container"><div id="2327922691_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2327922691_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2327922690" class="remote-video"><div id="player_2327922691" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2327922691" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" playsinline=""></video><audio id="audio2327922691" autoplay="" playsinline=""></audio></div></div></div>

            <div id="2327922692_container" class="user remote-stream-container"><div id="2327922692_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2327922692_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2327922692" class="remote-video"><div id="player_2327922692" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2327922692" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" playsinline=""></video><audio id="audio2327922692" autoplay="" playsinline=""></audio></div></div></div>

            <div id="2327922693_container" class="user remote-stream-container"><div id="2327922693_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2327922693_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2327922693" class="remote-video"><div id="player_2327922693" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2327922693" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" playsinline=""></video><audio id="audio2327922693" autoplay="" playsinline=""></audio></div></div></div>

            <div id="2327922694_container" class="user remote-stream-container"><div id="2327922694_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2327922694_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2327922694" class="remote-video"><div id="player_2327922694" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2327922694" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" playsinline=""></video><audio id="audio2327922694" autoplay="" playsinline=""></audio></div></div></div>

            <div id="2327922695_container" class="user remote-stream-container"><div id="2327922695_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2327922695_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2327922695" class="remote-video"><div id="player_2327922695" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2327922695" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" playsinline=""></video><audio id="audio2327922695" autoplay="" playsinline=""></audio></div></div></div>

            <div id="2327922696_container" class="user remote-stream-container"><div id="2327922696_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2327922696_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2327922696" class="remote-video"><div id="player_2327922696" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2327922696" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" playsinline=""></video><audio id="audio2327922696" autoplay="" playsinline=""></audio></div></div></div>

            <div id="2327922697_container" class="user remote-stream-container"><div id="2327922697_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2327922697_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2327922697" class="remote-video"><div id="player_2327922697" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2327922697" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" playsinline=""></video><audio id="audio2327922697" autoplay="" playsinline=""></audio></div></div></div> -->

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
    window.addEventListener('load', function() {
      window.agoraAppId = '<?php echo $agora->settings['appId'] ?>'; // set app id
      window.channelName = '<?php echo $channel->title() ?>'; // set channel name
      window.channelId = '<?php echo $channel->id() ?>'; // set channel name
      window.userID = parseInt(`${<?php echo $current_user->ID; ?>}`, 10);
      window.agoraMode = 'communication';

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