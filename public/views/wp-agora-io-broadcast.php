<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="agora agora-broadcast">
  <div id="main-container">
    <div id="screen-share-btn-container" class="col-2 float-right text-right mt-2">
      <button id="screen-share-btn"  type="button" class="btn btn-lg">
        <i id="screen-share-icon" class="fab fa-slideshare"></i>
      </button>
    </div>
    <div id="buttons-container" class="row justify-content-center mt-3">
      <div id="audio-controls" class="col-md-2 text-center btn-group">
        <button id="mic-btn" type="button" class="btn btn-block btn-dark btn-lg">
          <i id="mic-icon" class="fas fa-microphone"></i>
        </button>
        <button id="mic-dropdown" type="button" class="btn btn-lg btn-dark dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <div id="mic-list" class="dropdown-menu dropdown-menu-right">
          </div>
      </div>
      <div id="video-controls" class="col-md-2 text-center btn-group">
        <button id="video-btn"  type="button" class="btn btn-block btn-dark btn-lg">
          <i id="video-icon" class="fas fa-video"></i>
        </button>
        <button id="cam-dropdown" type="button" class="btn btn-lg btn-dark dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div id="camera-list" class="dropdown-menu dropdown-menu-right">
        </div>
      </div>
      <div class="col-md-2 text-center">
        <button id="exit-btn"  type="button" class="btn btn-block btn-danger btn-lg">
          <i id="exit-icon" class="fas fa-phone-slash"></i>
        </button>
      </div>
    </div>
    <div id="full-screen-video"></div>
    <div id="lower-ui-bar" class="row fixed-bottom mb-1">
      <div id="rtmp-btn-container" class="col ml-3 mb-2">
        <button id="rtmp-config-btn"  type="button" class="btn btn-primary btn-lg row rtmp-btn" data-toggle="modal" data-target="#addRtmpConfigModal">
          <i id="rtmp-config-icon" class="fas fa-rotate-270 fa-sign-out-alt"></i>
        </button>
        <button id="add-rtmp-btn"  type="button" class="btn btn-secondary btn-lg row rtmp-btn" data-toggle="modal" data-target="#add-external-source-modal">
          <i id="add-rtmp-icon" class="fas fa-plug"></i>
        </button>
      </div>
      <div id="external-broadcasts-container" class="container col-flex">
        <div id="rtmp-controlers" class="col">
          <!-- insert rtmp  controls -->
        </div>
      </div>
    </div>
  </div> <!--  end main-container  -->

  <script>
    /**
     * Agora Broadcast Client 
     */
    // console.log('Channel ID:', <?php echo $channel->id(); ?>);
    window.addEventListener('load', function() {
      
      window.channelName = '<?php echo $channel->title() ?>'; // set channel name
      console.log('Broadcast Channel:', window.channelName);
      
      var agoraAppId = '<?php echo $agora->settings['appId'] ?>'; // set app id
      /*

      // create client instance
      var client = AgoraRTC.createClient({mode: 'live', codec: 'vp8'}); // h264 better detail at a higher motion
      var mainStreamId; // reference to main stream

      // set video profile 
      // [full list: https://docs.agora.io/en/Interactive%20Broadcast/videoProfile_web?platform=Web#video-profile-table]
      var cameraVideoProfile = '720p_6'; // 960 × 720 @ 30fps  & 750kbs

      // keep track of streams
      var localStreams = {
        uid: '',
        camera: {
          camId: '',
          micId: '',
          stream: {}
        }
      };

      // keep track of devices
      var devices = {
        cameras: [],
        mics: []
      }

      var externalBroadcastUrl = '';

      // default config for rtmp
      var defaultConfigRTMP = {
        width: 640,
        height: 360,
        videoBitrate: 400,
        videoFramerate: 15,
        lowLatency: false,
        audioSampleRate: 48000,
        audioBitrate: 48,
        audioChannels: 1,
        videoGop: 30,
        videoCodecProfile: 100,
        userCount: 0,
        userConfigExtraInfo: {},
        backgroundColor: 0x000000,
        transcodingUsers: [],
      };

      // set log level:
      // -- .DEBUG for dev 
      // -- .NONE for prod
      AgoraRTC.Logger.setLogLevel(AgoraRTC.Logger.DEBUG); 

      // init Agora SDK
      client.init(agoraAppId, function () {
        console.log('AgoraRTC client initialized');
        // joinChannel(); // join channel upon successfull init
      }, function (err) {
        console.log('[ERROR] : AgoraRTC client init failed', err);
      });

      // client callbacks
      client.on('stream-published', function (evt) {
        console.log('Publish local stream successfully');
      });

      // when a remote stream is added
      client.on('stream-added', function (evt) {
        console.log('new stream added: ' + evt.stream.getId());
      });

      client.on('stream-removed', function (evt) {
        var stream = evt.stream;
        stream.stop(); // stop the stream
        stream.close(); // clean up and close the camera stream
        console.log("Remote stream is removed " + stream.getId());
      });

      //live transcoding events..
      client.on('liveStreamingStarted', function (evt) {
        console.log("Live streaming started");
      }); 

      client.on('liveStreamingFailed', function (evt) {
        console.log("Live streaming failed");
      }); 

      client.on('liveStreamingStopped', function (evt) {
        console.log("Live streaming stopped");
      });

      client.on('liveTranscodingUpdated', function (evt) {
        console.log("Live streaming updated");
      }); 

      // ingested live stream 
      client.on('streamInjectedStatus', function (evt) {
        console.log("Injected Steram Status Updated");
        console.log(JSON.stringify(evt));
      }); 

      // when a remote stream leaves the channel
      client.on('peer-leave', function(evt) {
        console.log('Remote stream has left the channel: ' + evt.stream.getId());
      });

      // show mute icon whenever a remote has muted their mic
      client.on('mute-audio', function (evt) {
        console.log('Mute Audio for: ' + evt.uid);
      });

      client.on('unmute-audio', function (evt) {
        console.log('Unmute Audio for: ' + evt.uid);
      });

      // show user icon whenever a remote has disabled their video
      client.on('mute-video', function (evt) {
        console.log('Mute Video for: ' + evt.uid);
      });

      client.on('unmute-video', function (evt) {
        console.log('Unmute Video for: ' + evt.uid);
      });
*/
    });
  </script>
</div>