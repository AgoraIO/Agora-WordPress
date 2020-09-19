<?php
$channelSettings    = $channel->get_properties();
$videoSettings      = $channelSettings['settings'];
$appearanceSettings = $channelSettings['appearance'];
$current_user       = wp_get_current_user();
?>
<?php $current_user = wp_get_current_user(); ?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
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

          <!-- <div id="2754018585_container" class="user remote-stream-container"><div id="2754018585_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2754018585_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2754018585" class="remote-video"><div id="player_2754018585" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2754018585" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" muted="" playsinline=""></video><audio id="audio2754018585" playsinline=""></audio></div></div></div>

          <div id="2754018543_container" class="user remote-stream-container"><div id="2754018585_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2754018585_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2754018585" class="remote-video"><div id="player_2754018585" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2754018585" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" muted="" playsinline=""></video><audio id="audio2754018585" playsinline=""></audio></div></div></div>

          <div id="2754018545_container" class="user remote-stream-container"><div id="2754018585_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2754018585_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2754018585" class="remote-video"><div id="player_2754018585" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2754018585" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" muted="" playsinline=""></video><audio id="audio2754018585" playsinline=""></audio></div></div></div>

          <div id="2754018544_container" class="user remote-stream-container"><div id="2754018585_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2754018585_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2754018585" class="remote-video"><div id="player_2754018585" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2754018585" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" muted="" playsinline=""></video><audio id="audio2754018585" playsinline=""></audio></div></div></div>

          <div id="2754018546_container" class="user remote-stream-container"><div id="2754018585_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2754018585_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2754018585" class="remote-video"><div id="player_2754018585" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2754018585" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" muted="" playsinline=""></video><audio id="audio2754018585" playsinline=""></audio></div></div></div>

          <div id="2754018547_container" class="user remote-stream-container"><div id="2754018585_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2754018585_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2754018585" class="remote-video"><div id="player_2754018585" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2754018585" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" muted="" playsinline=""></video><audio id="audio2754018585" playsinline=""></audio></div></div></div>

          <div id="2754018548_container" class="user remote-stream-container"><div id="2754018585_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2754018585_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2754018585" class="remote-video"><div id="player_2754018585" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2754018585" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" muted="" playsinline=""></video><audio id="audio2754018585" playsinline=""></audio></div></div></div>

          <div id="2754018549_container" class="user remote-stream-container"><div id="2754018585_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2754018585_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2754018585" class="remote-video"><div id="player_2754018585" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2754018585" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" muted="" playsinline=""></video><audio id="audio2754018585" playsinline=""></audio></div></div></div>

          <div id="2754018550_container" class="user remote-stream-container"><div id="2754018585_mute" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div><div id="2754018585_no-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div><div id="agora_remote_2754018585" class="remote-video"><div id="player_2754018585" style="width: 100%; height: 100%; position: relative; background-color: black; overflow: hidden;"><video id="video2754018585" style="width: 100%; height: 100%; position: absolute; object-fit: cover;" autoplay="" muted="" playsinline=""></video><audio id="audio2754018585" playsinline=""></audio></div></div></div> -->

        </div>
      </div>
    </div>

    <?php require_once "parts/footer-communication.php" ?>
  </section>

  <?php require_once "parts/scripts-common.php" ?>
  <script>
    window.agoraMode = 'communication';

    window.addEventListener('load', function() {
      window.agoraLogLevel = window.location.href.indexOf('localhost')>0 ? AgoraRTC.Logger.ERROR : AgoraRTC.Logger.NONE;
      AgoraRTC.Logger.setLogLevel(window.agoraLogLevel);
      window.AGORA_COMMUNICATION_CLIENT.initClientAndJoinChannel(window.agoraAppId, window.channelName);
    });
  </script>
</div>