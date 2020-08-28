<?php
$current_path = plugins_url('wp-agora-io') . '/public';
$channelSettings    = $channel->get_properties();
$videoSettings      = $channelSettings['settings'];
$appearanceSettings = $channelSettings['appearance'];
$recordingSettings  = $channelSettings['recording'];
$current_user       = wp_get_current_user();
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
  <div id="agora-root" class="agora agora-fullscreen">
    <section class="agora-container">
      <?php require_once "parts/header.php" ?>

      <div class="agora-content">
        <?php require_once "parts/header-controls.php" ?>

        <div id="screen-zone" class="screen">
          <div id="screen-users" class="screen-users screen-users-1">
            <div id="full-screen-video" class="user">
              <div id="mute-overlay" class="mute-overlay"><i class="fas fa-microphone-slash"></i></div>
              <div id="no-local-video" class="no-video-overlay text-center"><i class="fas fa-user"></i></div>
            </div>
          </div>
        </div>
      </div>
      <?php require_once "parts/footer-broadcast.php" ?>
    </section>
    

    <?php require_once "parts/modal-rtmp.php" ?>

    <?php require_once "parts/modal-external-url.php" ?>   
    
  </div>

  <?php wp_footer(); ?>
  <?php require_once "parts/scripts-common.php" ?>
  <script>
    
    window.agoraCurrentRole = 'host';
    window.agoraMode = 'broadcast';

    window.externalBroadcastUrl = '';

    // default config for rtmp
    window.defaultConfigRTMP = {
      width: <?php echo $videoSettings['external-width'] ?>,
      height: <?php echo $videoSettings['external-height'] ?>,
      videoBitrate: <?php echo $videoSettings['external-videoBitrate'] ?>,
      videoFramerate: <?php echo $videoSettings['external-videoFramerate'] ?>,
      lowLatency: <?php echo $videoSettings['external-lowLatency'] ?>,
      audioSampleRate: <?php echo $videoSettings['external-audioSampleRate'] ?>,
      audioBitrate: <?php echo $videoSettings['external-audioBitrate'] ?>,
      audioChannels: <?php echo $videoSettings['external-audioChannels'] ?>,
      videoGop: <?php echo $videoSettings['external-videoGop'] ?>,
      videoCodecProfile: <?php echo $videoSettings['external-videoCodecProfile'] ?>,
      userCount: 1,
      userConfigExtraInfo: {},
      backgroundColor: parseInt('<?php echo str_replace('#', '', $videoSettings['external-backgroundColor']) ?>', 16),
      transcodingUsers: [{
        uid: window.userID,
        alpha: 1,
        width: <?php echo $videoSettings['external-width'] ?>,
        height: <?php echo $videoSettings['external-height'] ?>,
        x: 0,
        y: 0,
        zOrder: 0
      }],
    };

    window.injectStreamConfig = {
      width: <?php echo $videoSettings['inject-width'] ?>,
      height: <?php echo $videoSettings['inject-height'] ?>,
      videoBitrate: <?php echo $videoSettings['inject-videoBitrate'] ?>,
      videoFramerate: <?php echo $videoSettings['inject-videoFramerate'] ?>,
      audioSampleRate: <?php echo $videoSettings['inject-audioSampleRate'] ?>,
      audioBitrate: <?php echo $videoSettings['inject-audioBitrate'] ?>,
      audioChannels: <?php echo $videoSettings['inject-audioChannels'] ?>,
      videoGop: <?php echo $videoSettings['inject-videoGop'] ?>,
    };

    window.addEventListener('load', function() {      
      
      window.mainStreamId; // reference to main stream

      // set video profile 
      // [full list: https://docs.agora.io/en/Interactive%20Broadcast/videoProfile_web?platform=Web#video-profile-table]
      
      // set log level:
      // -- .DEBUG for dev 
      // -- .NONE for prod
      // window.agoraLogLevel = window.location.href.indexOf('localhost')>0 ? AgoraRTC.Logger.DEBUG : AgoraRTC.Logger.ERROR;
      window.agoraLogLevel = window.location.href.indexOf('localhost')>0 ? AgoraRTC.Logger.ERROR : AgoraRTC.Logger.ERROR;
      AgoraRTC.Logger.setLogLevel(window.agoraLogLevel);
      // TODO: set DEBUG or NOE according to the current host (localhost or not)

      

      // init Agora SDK
      window.agoraClient.init(window.agoraAppId, function () {
        AgoraRTC.Logger.info('AgoraRTC client initialized');
        window.AGORA_BROADCAST_CLIENT.agoraJoinChannel(); // join channel upon successfull init
      }, function (err) {
        AgoraRTC.Logger.error('[ERROR] : AgoraRTC client init failed', err);
      });

      window.agoraClient.on('liveStreamingStarted', function (evt) {
        console.log("Live streaming started", evt);
      }); 

      window.agoraClient.on('liveStreamingFailed', function (evt) {
        console.log("Live streaming failed", evt);
      }); 

      window.agoraClient.on('liveStreamingStopped', function (evt) {
        console.log("Live streaming stopped", evt);
      });

      window.agoraClient.on('liveTranscodingUpdated', function (evt) {
        console.log("Live streaming updated", evt);
      });

      window.agoraClient.on('streamInjectedStatus', function (evt) {
        console.log("Live streaming Injected Status:", evt);
      });

      window.agoraClient.on('stream-added', function (evt) {
        console.log("streaming Injected:", evt);
      });
      window.agoraClient.on('exception', function (ex) {
        console.error("Agora Exception:", ex);
      });

    });// end addEventListener Load

  </script>
</body>
</html>