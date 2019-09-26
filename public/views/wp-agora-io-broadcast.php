<!-- Agora Broadcast View -->
<?php
$channelSettings    = $channel->get_properties();
$videoSettings      = $channelSettings['settings'];
$appearanceSettings = $channelSettings['appearance'];
$current_user       = wp_get_current_user();
?>
<div class="agora agora-broadcast">
  <div id="main-container" class="controls-top">
    <div id="full-screen-video">
      <div id="screen-share-btn-container" class="col-2 float-right text-right mt-3">
      <button id="screen-share-btn"  type="button" class="btn btn-md">
        <i id="screen-share-icon" class="fab fa-slideshare"></i>
      </button>
      </div>
      <div id="buttons-container" class="row justify-content-center mt-3">
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
          <button id="rtmp-config-btn"  type="button" class="btn btn-primary btn-md rtmp-btn" data-toggle="modal" data-target="#addRtmpConfigModal">
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
  </div> <!--  end main-container  -->
  <script>
    /**
     * Agora Broadcast Client 
     */
    window.addEventListener('load', function() {
      
      var agoraAppId = '<?php echo $agora->settings['appId'] ?>'; // set app id
      window.channelName = '<?php echo $channel->title() ?>'; // set channel name
      window.agoraCurrentRole = 'host';

      // default config for rtmp
      var defaultConfigRTMP = {
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
        userCount: 0,
        userConfigExtraInfo: {},
        backgroundColor: '<?php echo $videoSettings['external-backgroundColor'] ?>',
        transcodingUsers: [],
      };

      // create client instance
      window.agoraClient = AgoraRTC.createClient({mode: 'live', codec: 'vp8'}); // h264 better detail at a higher motion

      var mainStreamId; // reference to main stream

      // set video profile 
      // [full list: https://docs.agora.io/en/Interactive%20Broadcast/videoProfile_web?platform=Web#video-profile-table]
      window.cameraVideoProfile = '<?php echo $instance['videoprofile'] ?>';

      // keep track of streams
      window.localStreams = {
        uid: '',
        camera: {
          camId: '',
          micId: '',
          stream: {}
        }
      };

      // keep track of devices
      window.devices = {
        cameras: [],
        mics: []
      }

      window.externalBroadcastUrl = '';

      // set log level:
      // -- .DEBUG for dev 
      // -- .NONE for prod
      window.agoraLogLevel = window.location.href.indexOf('localhost')>0 ? AgoraRTC.Logger.DEBUG : AgoraRTC.Logger.ERROR;
      AgoraRTC.Logger.setLogLevel(window.agoraLogLevel);
      // TODO: set DEBUG or NOE according to the current host (localhost or not)

      // init Agora SDK
      window.agoraClient.init(agoraAppId, function () {
        AgoraRTC.Logger.info('AgoraRTC client initialized');
        agoraJoinChannel(); // join channel upon successfull init
      }, function (err) {
        AgoraRTC.Logger.error('[ERROR] : AgoraRTC client init failed', err);
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
      window.agoraClient.on('liveStreamingStarted', function (evt) {
        console.log("Live streaming started");
      }); 

      window.agoraClient.on('liveStreamingFailed', function (evt) {
        console.log("Live streaming failed");
      }); 

      window.agoraClient.on('liveStreamingStopped', function (evt) {
        console.log("Live streaming stopped");
      });

      window.agoraClient.on('liveTranscodingUpdated', function (evt) {
        console.log("Live streaming updated");
      }); 

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


    // use tokens for added security...
    function generateToken() {
      return <?php
      $appID = $agora->settings['appId'];
      $appCertificate = $agora->settings['appCertificate'];
      if($appCertificate && strlen($appCertificate)>0) {
        $channelName = $channel->title();
        $current_user = wp_get_current_user();
        $uid = $current_user->ID; // Get urrent user id

        // role should be based on the current user host...
        $settings = $channel->get_properties();
        $role = ($current_user->ID===(int)$settings['host']) ? 'host' : 'audience'; 
        $privilegeExpireTs = 0;
        echo '"'.RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpireTs). '"';
      } else {
        echo 'null';
      }
      ?>; // TODO: add a token generation
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