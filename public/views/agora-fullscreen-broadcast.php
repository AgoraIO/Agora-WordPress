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
<body class="agora custom-background-image">
  <?php
  $bgStyle = $instance['background']==='' ? '' : 'style="background-color:'.$instance['background'].'"';
  $bgClass = $instance['background']==='' ? 'gradient-4' : '';
  ?>
  <div class="agora-fullscreen-container controls-bottom window-mode <?php echo $bgClass ?>" <?php echo $bgStyle ?>>

    <div class="main-video-screen" id="full-screen-video">
      
      <div id="rejoin-container" class="rejoin-container" style="display: none">
        <button id="rejoin-btn" class="btn btn-primary btn-lg" type="button">
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <?php _e('Rejoin to this channel', 'agoraio'); ?>
        </button>
      </div>

      <div id="buttons-container">
        
        <div class="control-btn">
          <button id="mic-btn" type="button" class="btn btn-block btn-dark btn-xs" title="Mute Mic">
            <i id="mic-icon" class="fas fa-microphone"></i>
          </button>
        </div>
        <div class="control-btn main-btn">
          <button id="exit-btn"  type="button" class="btn btn-block btn-danger btn-xs" title="Finish Call">
            <i id="exit-icon" class="fas fa-phone-slash"></i>
          </button>
        </div>
        <div class="control-btn">
          <button id="video-btn"  type="button" class="btn btn-block btn-dark btn-xs" title="Mute Video">
            <i id="video-icon" class="fas fa-video"></i>
          </button>
        </div>

      </div>

    </div>

    <div id="lower-ui-bar" class="row mb-1">
      <?php if(is_array($recordingSettings) && 
            !empty($recordingSettings['bucket']) &&
            !empty($recordingSettings['accessKey'])) : ?>
        <div id="cloud-recording-container">
          <button id="cloud-recording-btn" class="btn btn-sm start-rec" title="<?php _e('Start Recording', 'agoraio'); ?>">
            <div class="inner-icon"> </div>
          </button>
        </div>
      <?php endif; ?>

      <div id="screen-share-btn-container" class="text-center control-btn">
        <button id="screen-share-btn"  type="button" class="btn btn-md" title="<?php _e('Screen Share', 'agoraio'); ?>">
          <i id="screen-share-icon" class="fas fa-share-square"></i>
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span>
        </button>
      </div>

      <div class="control-btn">
        <button id="rtmp-config-btn"  type="button" class="btn btn-primary btn-md rtmp-btn" 
          data-toggle="modal" data-target="#addRtmpConfigModal" title="<?php _e('Add RTMP Config', 'agoraio') ?>">
          <i id="rtmp-config-icon" class="fas fa-rotate-270 fa-sign-out-alt"></i>
        </button>
      </div>
      <div class="control-btn">
        <button id="add-rtmp-btn"  type="button" class="btn btn-secondary btn-md rtmp-btn" data-toggle="modal" data-target="#add-external-source-modal" title="<?php _e('Add External Source', 'agoraio') ?>">
          <i id="add-rtmp-icon" class="fas fa-plug"></i>
        </button>
      </div>
      <div id="external-broadcasts-container" class="container col-flex hidden">
        <div id="rtmp-controlers" class="col">
          <!-- insert rtmp  controls -->
        </div>
      </div>
    </div>
  </div>

  <!-- RTMP Config Modal -->
  <div class="modal fade slideInLeft animated" id="addRtmpConfigModal" tabindex="-1" role="dialog" aria-labelledby="rtmpConfigLabel" aria-hidden="true" data-keyboard=true>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="rtmpConfigLabel"><i class="fas fa-sliders-h"></i></h5>
          <button type="button" class="close" data-dismiss="modal" data-reset="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="rtmp-config" action="" method="post" onSubmit="return false;">
            <div class="form-group">
              <label for="input_rtmp_url">RTMP Server URL</label>
              <input type="url" class="form-control" id="input_rtmp_url" placeholder="Enter the RTMP Server URL" value="" required />
            </div>
            <div class="form-group">
              <label for="input_private_key">Stream key</label>
              <input type="text" class="form-control" id="input_private_key" placeholder="Enter stream key" required />
            </div>
            <input type="submit" value="Start RTMP" style="position:fixed; top:-999999px">
          </form>
        </div>
        <div class="modal-footer">
          <span id="rtmp-error-msg" class="error text-danger" style="display: none">Please complete the information!</span>
          <button type="button" id="start-RTMP-broadcast" class="btn btn-primary">
            <i class="fas fa-satellite-dish"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- end Modal -->

  <!-- External Injest Url Modal -->
  <div class="modal fade slideInLeft animated" id="add-external-source-modal" tabindex="-1" role="dialog" aria-labelledby="add-external-source-url-label" aria-hidden="true" data-keyboard=true>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="add-external-source-url-label">
            <i class="fas fa-broadcast-tower"></i> [add external url]
          </h5>
          <button id="hide-external-url-modal" type="button" class="close" data-dismiss="modal" data-reset="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="external-inject-config">
            <div class="form-group">
              <label for="input_external_url">External URL</label>
              <input type="url" class="form-control" id="input_external_url" placeholder="Enter the external URL" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <span id="external-url-error" class="error text-danger" style="display: none">Please enter a valid external URL</span>
          <button type="button" id="add-external-stream" class="btn btn-primary">
              <i id="add-rtmp-icon" class="fas fa-plug"></i>  
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- end Modal -->

  <?php wp_footer(); ?>
  <script>
    window.AGORA_TOKEN_UTILS = {
      agoraGenerateToken: agoraGenerateToken
    };
    // video profile settings
    window.cameraVideoProfile = '<?php echo $instance['videoprofile'] ?>'; // 640x480 @ 30fps & 750kbs
    window.screenVideoProfile = '<?php echo $instance['screenprofile'] ?>';
    window.addEventListener('load', function() {
      window.agoraAppId = '<?php echo $agora->settings['appId'] ?>'; // set app id
      window.channelName = '<?php echo $channel->title() ?>'; // set channel name
      window.channelId = '<?php echo $channel->id() ?>'; // set channel name
      window.agoraCurrentRole = 'host';
      window.agoraMode = 'audience';
      window.userID = parseInt(`${<?php echo $current_user->ID; ?>}`, 10);
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
        console.log("Agora Exception:", ex);
      });

    });// end addEventListener Load


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
  </script>
</body>
</html>