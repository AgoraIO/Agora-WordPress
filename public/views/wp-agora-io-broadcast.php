<!-- Agora Broadcast View -->
<?php
$channelSettings    = $channel->get_properties();
$videoSettings      = $channelSettings['settings'];
$appearanceSettings = $channelSettings['appearance'];
$recordingSettings = $channelSettings['recording'];
$current_user       = wp_get_current_user();
?>
<div class="agora agora-broadcast">
  <div id="main-container" class="controls-top">
    <div id="full-screen-video">
      <?php if(is_array($recordingSettings) && 
            !empty($recordingSettings['bucket']) &&
            !empty($recordingSettings['accessKey'])) : ?>
      <div id="cloud-recording-container" class="mt-3">
        <button id="cloud-recording-btn" class="btn btn-sm start-rec" title="<?php _e('Start Recording', 'agoraio'); ?>">
          <div class="inner-icon"> </div>
        </button>
      </div>
      <?php endif; ?>
      <div id="screen-share-btn-container" class="col-2 float-right text-right mt-3">
        <button id="screen-share-btn"  type="button" class="btn btn-md" title="<?php _e('Screen Share', 'agoraio'); ?>">
          <i id="screen-share-icon" class="fab fa-slideshare"></i>
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span>
        </button>
      </div>
      <div id="buttons-container" class="row justify-content-center mt-3" style="display: none">
        <div id="audio-controls" class="col-md-2 text-center btn-group">
          <button id="mic-btn" type="button" class="btn btn-block btn-dark btn-md">
            <i id="mic-icon" class="fas fa-microphone"></i>
          </button>
          <button id="mic-dropdown" type="button" class="btn btn-md btn-dark dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <span class="sr-only"><?php _e('Toggle Dropdown', 'agoraio'); ?></span>
            </button>
            <div id="mic-list" class="dropdown-menu dropdown-menu-right">
            </div>
        </div>
        <div id="video-controls" class="col-md-2 text-center btn-group">
          <button id="video-btn"  type="button" class="btn btn-block btn-dark btn-md">
            <i id="video-icon" class="fas fa-video"></i>
          </button>
          <button id="cam-dropdown" type="button" class="btn btn-md btn-dark dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="sr-only"><?php _e('Toggle Dropdown', 'agoraio'); ?></span>
          </button>
          <div id="camera-list" class="dropdown-menu dropdown-menu-right">
          </div>
        </div>
        <div class="col-md-2 text-center">
          <button id="exit-btn"  type="button" class="btn btn-block btn-danger btn-md">
            <i id="exit-icon" class="fas fa-phone-slash"></i>
          </button>
        </div>
      </div>

      <div id="lower-ui-bar" class="row mb-1">
        <div id="rtmp-btn-container" class="col ml-3 mb-2">
          <button id="rtmp-config-btn"  type="button" class="btn btn-primary btn-md rtmp-btn" 
            data-toggle="modal" data-target="#addRtmpConfigModal">
            <i id="rtmp-config-icon" class="fas fa-rotate-270 fa-sign-out-alt"></i>
          </button>
          <button id="add-rtmp-btn"  type="button" class="btn btn-secondary btn-md rtmp-btn" data-toggle="modal" data-target="#add-external-source-modal">
            <i id="add-rtmp-icon" class="fas fa-plug"></i>
          </button>
        </div>
        <div id="external-broadcasts-container" class="container col-flex">
          <div id="rtmp-controlers" class="col">
            <!-- insert rtmp  controls -->
          </div>
        </div>
      </div>
    </div> <!--  end full-screen-video -->


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

  </div> <!--  end main-container  -->


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

      <?php /*
      // client callbacks
      window.agoraClient.on('stream-published', function (evt) {
        console.log('Publish local stream successfully');
      });

      // when a remote stream is added
      window.agoraClient.on('stream-added', function (evt) {
        console.log('new stream added: ' + evt.stream.getId());
      });

      window.agoraClient.on('stream-removed', function (evt) {
        var stream = evt.stream;
        stream.stop(); // stop the stream
        stream.close(); // clean up and close the camera stream
        console.log("Remote stream is removed " + stream.getId());
      });

      //live transcoding events..
      

      // ingested live stream 
      window.agoraClient.on('streamInjectedStatus', function (evt) {
        console.log("Injected Steram Status Updated");
        console.log(JSON.stringify(evt));
      }); 

      // when a remote stream leaves the channel
      window.agoraClient.on('peer-leave', function(evt) {
        console.log('Remote stream has left the channel: ' + evt.stream.getId());
      });

      // show mute icon whenever a remote has muted their mic
      window.agoraClient.on('mute-audio', function (evt) {
        console.log('Mute Audio for: ' + evt.uid);
      });

      window.agoraClient.on('unmute-audio', function (evt) {
        console.log('Unmute Audio for: ' + evt.uid);
      });

      // show user icon whenever a remote has disabled their video
      window.agoraClient.on('mute-video', function (evt) {
        console.log('Mute Video for: ' + evt.uid);
      });

      window.agoraClient.on('unmute-video', function (evt) {
        console.log('Unmute Video for: ' + evt.uid);
      });

      */ ?>
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