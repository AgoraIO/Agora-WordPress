<?php
$settings = $channel->get_properties();
$agoraStyle = '';
if (!empty($settings['appearance']['splashImageURL'])) {
  $agoraStyle = 'style="background-size:cover;background-position:center center; background-image:url('.$settings['appearance']['splashImageURL'].')"';
}
$buttonText = __('Watch the Live Stream', 'agoraio'); // watchButtonText
if(!empty($settings['appearance']['watchButtonText'])) {
  $buttonText = $settings['appearance']['watchButtonText'];
}
$buttonIcon = $settings['appearance']['watchButtonIcon']!=='false';

$screenStyles = '';
if (!empty($settings['appearance']['noHostImageURL'])) {
  $screenStyles = "background-size:cover; background-image: url('".$settings['appearance']['noHostImageURL']."')";
}

// die("<pre>".print_r($settings, true)."</pre>");
$user_avatar = get_avatar_data( $settings['host'], array('size' => 168) );
// die("<pre>".print_r($user_avatar['url'], true)."</pre>");
?>
<div id="agora-root" class="agora agora-broadcast agora-audience">
  <section class="agora-container no-footer">
    <?php require_once "parts/header.php" ?>

    <div class="agora-content">
      <?php require_once "parts/header-controls.php" ?>

      <div id="screen-zone" class="screen" <?php echo $agoraStyle ?>>
        <div id="screen-users" class="screen-users screen-users-1">
          <div id="full-screen-video" class="user" style="display: none; <?php echo $screenStyles; ?>"></div>

          <div id="watch-live-overlay" class="overlay user">
            <div id="overlay-container">
              <button id="watch-live-btn" type="button" class="room-title">
                <?php if($buttonIcon) { ?>
                  <i id="watch-live-icon" class="fas fa-broadcast-tower"></i>
                <?php } ?>
                <span><?php echo $buttonText ?></span>
              </button>
            </div>
          </div>
          <div id="watch-live-closed" class="overlay user" style="display: none">
            <div id="overlay-container">
              <button id="watch-live--btn" type="button" class="room-title">
                <?php if($buttonIcon) { ?>
                  <i id="watch-live-icon" class="fas fa-broadcast-tower"></i>
                <?php } ?>
                <span>The Live Stream has finished</span>
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

  </section>

  <?php require_once "parts/scripts-common.php" ?>
  <script>
    window.agoraCurrentRole = 'audience';
    window.agoraMode = 'audience';
    window.remoteStreams = {};

    // set log level:
    // -- .DEBUG for dev 
    // -- .NONE for prod
    window.agoraLogLevel = window.location.href.indexOf('localhost')>0 ? AgoraRTC.Logger.ERROR : AgoraRTC.Logger.ERROR;
    AgoraRTC.Logger.setLogLevel(window.agoraLogLevel);
    // window.AGORA_BROADCAST_UI.calculateVideoScreenSize();


    window.addEventListener('load', function() {
      // create client, vp8 to work across mobile devices
      window.agoraClient = AgoraRTC.createClient({mode: 'live', codec: 'vp8'});

      window.AGORA_RTM_UTILS.setupRTM(window.agoraAppId, window.channelName);

      jQuery('#fullscreen-expand').click(window.AGORA_UTILS.toggleFullscreen);

      const exitBtn = jQuery('#exit-btn')
      exitBtn.hide();
      exitBtn.click(function() {
        Object.values(window.remoteStreams).forEach(stream => stream.close());
        window.remoteStreams = {};
        finishVideoScreen();
      })
      function finishVideoScreen() {
        jQuery("#full-screen-video").hide();
        jQuery("#watch-live-closed").show();
        jQuery("#watch-live-closed").show();
        exitBtn.hide();
      }
      
      // Due to broswer restrictions on auto-playing video, 
      // user must click to init and join channel
      jQuery("#watch-live-btn").click(function(){
        AgoraRTC.Logger.info("user clicked to watch broadcast");

        // init Agora SDK
        window.agoraClient.init(agoraAppId, function () {
          jQuery("#watch-live-overlay").remove();
          jQuery("#screen-zone").css('background', 'none')
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
      window.agoraClient.on('stream-added', function addStream(evt) {
        const stream = evt.stream;
        const streamId = stream.getId();
        window.remoteStreams[streamId] = stream;

        AgoraRTC.Logger.info('New stream added: ' + streamId);
        AgoraRTC.Logger.info('Subscribing to remote stream:' + streamId);

        jQuery("#watch-live-closed").hide();
        jQuery("#watch-live-overlay").hide();
        jQuery("#full-screen-video").css('background', 'none').fadeIn();
        exitBtn.show();

        // Subscribe to the stream.
        window.agoraClient.subscribe(stream, function (err) {
          AgoraRTC.Logger.error('[ERROR] : subscribe stream failed', err);
        });
      });

      window.agoraClient.on('stream-removed', function closeStream(evt) {
        const stream = evt.stream;
        const streamId = stream.getId();
        stream.stop(); // stop the stream
        stream.close(); // clean up and close the camera stream

        window.remoteStreams[streamId] = null;
        delete window.remoteStreams[streamId];

        AgoraRTC.Logger.warning("Remote stream is removed " + stream.getId());
      });

      window.agoraClient.on('stream-subscribed', function (evt) {
        const remoteStream = evt.stream;
        const streamId = remoteStream.getId();
        AgoraRTC.Logger.info('Successfully subscribed to remote stream: ' + streamId);

        if (window.screenshareClients[streamId]) {
          // this is a screen share stream:
          console.log('Screen stream arrived:');
          window.AGORA_SCREENSHARE_UTILS.addRemoteScreenshare(remoteStream);
        } else {
          const streamsContainer = jQuery('#full-screen-video');
          streamsContainer.append(
            jQuery('<div/>', {'id': streamId + '_container',  'class': 'user remote-stream-container'}).append(
              jQuery('<div/>', {'id': streamId + '_mute', 'class': 'mute-overlay'}).append(
                  jQuery('<i/>', {'class': 'fas fa-microphone-slash'})
              ),
              jQuery('<div/>', {'id': streamId + '_no-video', 'class': 'no-video-overlay text-center'}).append(
                jQuery('<i/>', {'class': 'fas fa-user'})
              ),
              jQuery('<div/>', {'id': 'agora_remote_' + streamId, 'class': 'remote-video'})
            )
          );

          remoteStream.play('agora_remote_' + streamId);
          // remoteStream.play('full-screen-video');
        }
      });

      // remove the remote-container when a user leaves the channel
      window.agoraClient.on('peer-leave', function(evt) {
        AgoraRTC.Logger.info('Remote stream has left the channel: ' + evt.uid);
        
        if (!evt || !evt.stream) {
          console.error('Stream undefined cannot be removed', evt);
          return false;
        }

        // debugger;
        const streamId = evt.stream.getId(); // the the stream id
        evt.stream.isPlaying() && evt.stream.stop(); // stop the stream
        // jQuery('#uid-'+streamId).remove();

        if(window.remoteStreams[streamId] !== undefined) {
          window.remoteStreams[streamId].isPlaying() && window.remoteStreams[streamId].stop(); // stop playing the feed
          delete window.remoteStreams[streamId]; // remove stream from list

          const usersCount = Object.keys(window.remoteStreams).length;
          window.AGORA_UTILS.updateUsersCounter(usersCount)
        }

        if (window.screenshareClients[streamId]) {
          if (typeof window.screenshareClients[streamId].stop==='function') {
            window.screenshareClients[streamId].isPlaying() && window.screenshareClients[streamId].stop();
          }
          const remoteContainerID = '#' + streamId + '_container';
          jQuery(remoteContainerID).empty().remove();
          const streamsContainer = jQuery('#screen-zone');
          streamsContainer.toggleClass('sharescreen');
          delete window.screenshareClients[streamId];
        } else {
          if (jQuery('#full-screen-video').children().length>=0) {
            finishVideoScreen();
          }
        }
      });


      // show mute icon whenever a remote has muted their mic
      window.agoraClient.on("mute-audio", function (evt) {
        window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_mute', true);
      });

      window.agoraClient.on("unmute-audio", function (evt) {
        window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_mute', false);
      });

      // show user icon whenever a remote has disabled their video
      window.agoraClient.on("mute-video", function (evt) {
        window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_no-video', true);
      });

      window.agoraClient.on("unmute-video", function (evt) {
        window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_no-video', false);
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
      const token = window.AGORA_TOKEN_UTILS.agoraGenerateToken();

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
          window.AGORA_RTM_UTILS.joinChannel(uid);
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
  </script>
</div>