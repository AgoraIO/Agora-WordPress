<?php
$settings = $channel->get_properties();
$agoraStyle = '';
if (!empty($settings['appearance']['splashImageURL'])) {
  $agoraStyle = 'style="background-image:url('.$settings['appearance']['splashImageURL'].')"';
}
$buttonText = __('Watch the Live Stream', 'agoraio'); // watchButtonText
if(!empty($settings['appearance']['watchButtonText'])) {
  $buttonText = $settings['appearance']['watchButtonText'];
}
$buttonIcon = $settings['appearance']['watchButtonIcon']!=='false';

$screenStyles = '';
if (!empty($settings['appearance']['noHostImageURL'])) {
  $screenStyles = "background-image: url('".$settings['appearance']['noHostImageURL']."')";
}

// die("<pre>".print_r($settings, true)."</pre>");
$user_avatar = get_avatar_data( $settings['host'], array('size' => 168) );
// die("<pre>".print_r($user_avatar['url'], true)."</pre>");
?>
<div class="agora agora-broadcast agora-audience" <?php echo $agoraStyle ?>>
  <div class="container-fluid p-0">
    <div id="full-screen-video" style="display: none; <?php echo $screenStyles; ?>"></div>
    <div id="watch-live-overlay" class="overlay">
      <div id="overlay-container">
          <div class="col-md text-center">
            <button id="watch-live-btn" type="button" class="btn btn-block btn-primary btn-xlg">
              <?php if($buttonIcon) { ?>
                <i id="watch-live-icon" class="fas fa-broadcast-tower"></i>
              <?php } ?>
              <span><?php echo $buttonText ?></span>
            </button>
          </div>
      </div>
    </div>
    <div id="watch-live-closed" class="overlay" style="display: none">
      <div id="overlay-container">
          <div class="col-md text-center">
            <button id="watch-live--btn" type="button" class="btn btn-block btn-primary btn-xlg">
              <i id="watch-live-icon" class="fas fa-broadcast-tower"></i><span>The Live Stream has finished</span>
            </button>
          </div>
      </div>
    </div>
    <?php if ($user_avatar) : ?>
    <div id="user_gravatar_wrapper" class="user_gravatar_wrapper d-none">
      <div class="user_gravatar_circle">
        <img src="<?php echo $user_avatar['url'] ?>" alt="Broadcast User">
      </div>
    </div>
    <?php endif; ?>
  </div>
  <script>
    window.addEventListener('load', function() {
      var agoraAppId = '<?php echo $agora->settings['appId'] ?>'; // set app id
      window.channelName = '<?php echo $channel->title() ?>'; // set channel name
      window.channelId = '<?php echo $channel->id() ?>'; // set channel name
      window.agoraCurrentRole = 'audience';
      window.agoraMode = 'audience';

      // create client 
      // vp8 to work across mobile devices
      window.agoraClient = AgoraRTC.createClient({mode: 'live', codec: 'vp8'});
      window.cameraVideoProfile = '<?php echo $instance['videoprofile'] ?>';
      window.screenVideoProfile = '<?php echo $instance['screenprofile'] ?>';

      // set log level:
      // -- .DEBUG for dev 
      // -- .NONE for prod
      window.agoraLogLevel = window.location.href.indexOf('localhost')>0 ? AgoraRTC.Logger.DEBUG : AgoraRTC.Logger.ERROR;
      AgoraRTC.Logger.setLogLevel(window.agoraLogLevel);
      window.AGORA_BROADCAST_UI.calculateVideoScreenSize();
      
      // Due to broswer restrictions on auto-playing video, 
      // user must click to init and join channel
      jQuery("#watch-live-btn").click(function(){
        AgoraRTC.Logger.info("user clicked to watch broadcast");

        // init Agora SDK
        window.agoraClient.init(agoraAppId, function () {
          jQuery("#watch-live-overlay").remove();
          jQuery("#full-screen-video").fadeIn();
          AgoraRTC.Logger.info('AgoraRTC client initialized');
          joinChannel(); // join channel upon successfull init
        }, function (err) {
          AgoraRTC.Logger.error('[ERROR] : AgoraRTC client init failed', err);
        });
      });

      window.agoraClient.on('stream-published', function (evt) {
        AgoraRTC.Logger.info('Publish local stream successfully');
      });

      // connect remote streams
      window.agoraClient.on('stream-added', function (evt) {
        var stream = evt.stream;
        var streamId = stream.getId();
        AgoraRTC.Logger.info('New stream added: ' + streamId);
        AgoraRTC.Logger.info('Subscribing to remote stream:' + streamId);
        jQuery("#watch-live-closed").hide();
        jQuery("#full-screen-video").fadeIn();
        // Subscribe to the stream.
        window.agoraClient.subscribe(stream, function (err) {
          AgoraRTC.Logger.error('[ERROR] : subscribe stream failed', err);
        });
      });

      window.agoraClient.on('stream-removed', function (evt) {
        var stream = evt.stream;
        stream.stop(); // stop the stream
        stream.close(); // clean up and close the camera stream
        AgoraRTC.Logger.warning("Remote stream is removed " + stream.getId());
      });

      window.agoraClient.on('stream-subscribed', function (evt) {
        var remoteStream = evt.stream;
        remoteStream.play('full-screen-video');
        AgoraRTC.Logger.info('Successfully subscribed to remote stream: ' + remoteStream.getId());
      });

      // remove the remote-container when a user leaves the channel
      window.agoraClient.on('peer-leave', function(evt) {
        AgoraRTC.Logger.info('Remote stream has left the channel: ' + evt.uid);
        evt.stream.stop(); // stop the stream
        if (jQuery('#full-screen-video').children().length===0) {
          jQuery("#full-screen-video").fadeOut();
          jQuery("#watch-live-closed").show();
        }

      });

      // show mute icon whenever a remote has muted their mic
      window.agoraClient.on('mute-audio', function (evt) {
        var remoteId = evt.uid;
      });

      window.agoraClient.on('unmute-audio', function (evt) {
        var remoteId = evt.uid;
      });

      // show user icon whenever a remote has disabled their video
      window.agoraClient.on('mute-video', function (evt) {
        var remoteId = evt.uid;
        //console.log('Mute video from remote:', remoteId);
        // jQuery('#user_gravatar_wrapper').toggleClass('d-none');
        // jQuery('#full-screen-video').children().eq(0).hide();
      });

      window.agoraClient.on('unmute-video', function (evt) {
        var remoteId = evt.uid;
        // console.log('Unmute video from remote:', remoteId);
        // jQuery('#user_gravatar_wrapper').toggleClass('d-none');
        // jQuery('#full-screen-video').children().eq(0).show();
      });

      // ingested live stream 
      window.agoraClient.on('streamInjectedStatus', function (evt) {
        AgoraRTC.Logger.info("Injected Steram Status Updated");
        // evt.stream.play('full-screen-video');
        AgoraRTC.Logger.info(JSON.stringify(evt));
      }); 
    });

    // join a channel
    function joinChannel() {
      var token = agoraGenerateToken();

      // set the role
      window.agoraClient.setClientRole('audience', function() {
        AgoraRTC.Logger.info('Client role set to audience');
      }, function(e) {
        AgoraRTC.Logger.error('setClientRole failed', e);
      });
      
      <?php
      $current_user = wp_get_current_user();
      $uid = $current_user->ID; // Get urrent user id
      echo "var userID = ".$uid.";\n";
      ?>
      window.agoraClient.join(token, channelName, userID, function(uid) {
          AgoraRTC.Logger.info('User ' + uid + ' join channel successfully');
      }, function(err) {
          AgoraRTC.Logger.error('[ERROR] : join channel failed', err);
      });
    }

    function agoraLeaveChannel() {
      window.agoraClient.leave(function() {
        AgoraRTC.Logger.info('client leaves channel');
      }, function(err) {
        AgoraRTC.Logger.error('client leave failed ', err); //error handling
      });
    }

    // use tokens for added security
    function agoraGenerateToken() {
      return <?php
      $appID = $agora->settings['appId'];
      $appCertificate = $agora->settings['appCertificate'];
      if($appCertificate && strlen($appCertificate)>0) {
        $channelName = $channel->title();
        $current_user = wp_get_current_user();
        $uid = $current_user->ID; // Get urrent user id

        // role should be based on the current user host...
        $role = 2; // Subscriber
        $privilegeExpireTs = 0;
        echo '"'.AgoraRtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpireTs). '"';
      } else {
        echo 'null';
      }
      ?>;
    }
  </script>
</div>