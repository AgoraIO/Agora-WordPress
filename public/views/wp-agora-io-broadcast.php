<!-- Agora Broadcast View -->
<?php
$channelSettings    = $channel->get_properties();
$videoSettings      = $channelSettings['settings'];
$appearanceSettings = $channelSettings['appearance'];
$recordingSettings = $channelSettings['recording'];
$current_user       = wp_get_current_user();
?>
<div class="agora agora-broadcast">
      <?php /* if(is_array($recordingSettings) && 
            !empty($recordingSettings['bucket']) &&
            !empty($recordingSettings['accessKey'])) : ?>
      <!-- <div id="cloud-recording-container" class="mt-3">
        <button id="cloud-recording-btn" class="btn btn-sm start-rec" title="<?php _e('Start Recording', 'agoraio'); ?>">
          <div class="inner-icon"> </div>
        </button>
      </div> -->
      <?php endif; */ ?>

    <section class="agora-container">
      <?php require_once "parts/header.php" ?>

      <div class="agora-content">
        <?php require_once "parts/header-controls.php" ?>

        <div class="screen">
          <div id="screen-users" class="screen-users screen-users-1">
            <div id="full-screen-video" class="user"></div>
          </div>
        </div>
      </div>

      <?php require_once "parts/footer.php" ?>
    </section>


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


  <script>
    /**
     * Agora Broadcast Client 
     */
    window.addEventListener('load', function() {
      
      window.agoraAppId = '<?php echo $agora->settings['appId'] ?>'; // set app id
      window.channelName = '<?php echo $channel->title() ?>'; // set channel name
      window.channelId = '<?php echo $channel->id() ?>'; // set channel name
      window.agoraCurrentRole = 'host';
      window.agoraMode = 'audience';
      window.userID = parseInt(`${<?php echo $current_user->ID; ?>}`, 10);

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
      window.agoraLogLevel = window.location.href.indexOf('local')>0 ? AgoraRTC.Logger.DEBUG : AgoraRTC.Logger.ERROR;
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
    });

    window.AGORA_TOKEN_UTILS = {
      agoraGenerateToken: agoraGenerateToken
    }
    // use tokens for added security...
    function agoraGenerateToken() {
      return <?php
      $appID = $agora->settings['appId'];
      $appCertificate = $agora->settings['appCertificate'];
      if($appCertificate && strlen($appCertificate)>0) {
        $channelName = $channel->title();
        $current_user = wp_get_current_user();
        $uid = $current_user->ID; // Get urrent user id

        // role should be based on the current user host...
        $settings = $channel->get_properties();
        $role = 1;
        $privilegeExpireTs = 0;
        echo '"'.AgoraRtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpireTs). '"';
      } else {
        echo 'null';
      }
      ?>;
    }
  </script>
  <style>
    <?php if (isset($appearanceSettings['activeButtonColor']) && $appearanceSettings['activeButtonColor']!=='') { ?>
    .agora #main-container .btn.btn-dark,
    .agora #main-container .btn.btn-dark:hover,
    .agora #main-container .btn.btn-dark:focus {
      background-color: <?php echo $appearanceSettings['activeButtonColor'] ?>;
      border-color: <?php echo $appearanceSettings['activeButtonColor'] ?>;
    }
    <?php } ?>
    <?php if (isset($appearanceSettings['disabledButtonColor']) && $appearanceSettings['disabledButtonColor']!=='') { ?>
    .agora #main-container .btn.btn-danger,
    .agora #main-container .btn.btn-danger:hover,
    .agora #main-container .btn.btn-danger:focus {
      background-color: <?php echo $appearanceSettings['disabledButtonColor'] ?>;
      border-color: <?php echo $appearanceSettings['disabledButtonColor'] ?>;
    }
    <?php } ?>
  </style>
</div>
<!-- End Agora Broadcast View -->