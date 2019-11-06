<?php
$current_path = plugins_url('wp-agora-io') . '/public';
$channelSettings    = $channel->get_properties();
$videoSettings      = $channelSettings['settings'];
$appearanceSettings = $channelSettings['appearance'];
$recordingSettings = $channelSettings['recording'];
$current_user       = wp_get_current_user();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Agora.io Communication Chat</title>
  <?php wp_head() ?>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
  <link rel="stylesheet" href="<?php echo $current_path ?>/css/wp-agora-fullscreen.css">
</head>
<body class="agora custom-background-image">
  <div class="agora-fullscreen-container controls-bottom window-mode gradient-x">

    <div class="main-video-screen" id="full-screen-video">
      
      <div id="rejoin-container" class="rejoin-container" style="display: none">
        <button id="rejoin-btn" class="btn btn-primary btn-lg" type="button">
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <?php _e('Rejoin to this channel', 'agoraio'); ?>
        </button>
      </div>

      <div id="buttons-container" class="row justify-content-center mt-3">
        <div class="col-md-2 text-center control-btn">
          <button id="mic-btn" type="button" class="btn btn-block btn-dark btn-xs">
            <i id="mic-icon" class="fas fa-microphone"></i>
          </button>
        </div>
        <div class="col-md-2 text-center control-btn main-btn">
          <button id="exit-btn"  type="button" class="btn btn-block btn-danger btn-xs">
            <i id="exit-icon" class="fas fa-phone-slash"></i>
          </button>
        </div>
        <div class="col-md-2 text-center control-btn">
          <button id="video-btn"  type="button" class="btn btn-block btn-dark btn-xs">
            <i id="video-icon" class="fas fa-video"></i>
          </button>
        </div>
      </div>

    </div>
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
      window.agoraCurrentRole = 'host';
      window.agoraMode = 'audience';
      window.userID = parseInt(`123${<?php echo $current_user->ID; ?>}`, 10);
      window.agoraMode = 'broadcast';

      // create client instance
      window.agoraClient = AgoraRTC.createClient({mode: 'live', codec: 'vp8'}); // h264 better detail at a higher motion
      window.screenClient = AgoraRTC.createClient({mode: 'rtc', codec: 'vp8'}); 

      window.mainStreamId; // reference to main stream

      // set video profile 
      // [full list: https://docs.agora.io/en/Interactive%20Broadcast/videoProfile_web?platform=Web#video-profile-table]
      window.cameraVideoProfile = '<?php echo $instance['videoprofile'] ?>';
      window.screenVideoProfile = '<?php echo $instance['screenprofile'] ?>';

      // keep track of streams
      window.localStreams = {
        uid: '',
        camera: {
          camId: '',
          micId: '',
          stream: {}
        },
        screen: {
          id: "",
          stream: {}
        }
      };

      // keep track of devices
      window.devices = {
        cameras: [],
        mics: []
      }

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


      // set log level:
      // -- .DEBUG for dev 
      // -- .NONE for prod
      window.agoraLogLevel = window.location.href.indexOf('localhost')>0 ? AgoraRTC.Logger.DEBUG : AgoraRTC.Logger.ERROR;
      AgoraRTC.Logger.setLogLevel(window.agoraLogLevel);
      // TODO: set DEBUG or NOE according to the current host (localhost or not)

      // init Agora SDK
      window.agoraClient.init(window.agoraAppId, function () {
        AgoraRTC.Logger.info('AgoraRTC client initialized');
        agoraJoinChannel(); // join channel upon successfull init
      }, function (err) {
        AgoraRTC.Logger.error('[ERROR] : AgoraRTC client init failed', err);
      });

    });// end addEventListener


    // use tokens for added security
    function generateToken() {
      return <?php
      $appID = $agora->settings['appId'];
      $appCertificate = $agora->settings['appCertificate'];
      $current_user = wp_get_current_user();

      if($appCertificate && strlen($appCertificate)>0) {
        $channelName = $channel->title();
        $uid = 0; // $current_user->ID; // Get urrent user id

        // role should be based on the current user host...
        $settings = $channel->get_properties();
        $role = 'Role_Subscriber';
        $privilegeExpireTs = 0;
        if(!class_exists('RtcTokenBuilder')) {
          require_once(__DIR__.'/../../includes/token-server/RtcTokenBuilder.php');
        }
        echo '"'.RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpireTs). '"';
      } else {
        echo 'null';
      }
      ?>;
    }
  </script>
  <script src="<?php echo $current_path ?>/js/agora-broadcast-client.js"></script>
  <script src="<?php echo $current_path ?>/js/broadcast-ui.js"></script>
</body>
</html>