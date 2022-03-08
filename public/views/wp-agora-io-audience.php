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

$remoteSpeakersPos = isset($agora->settings['agora-remote-speakers-position']) ? $agora->settings['agora-remote-speakers-position'] : '';

// $user_avatar = get_avatar_data( $settings['host'], array('size' => 168) );
?>
<div id="agora-root" class="agora agora-broadcast agora-audience agora-default-template agora-default-template-screen-users-<?php if($remoteSpeakersPos == '') { echo 'top'; } else { echo $remoteSpeakersPos; } ?>">
  <section class="agora-container no-footer">
    <?php require_once "parts/header.php" ?>

    <div class="agora-content">
      <?php require_once "parts/header-controls.php" ?>

      <div id="screen-zone" class="screen agora-screen-users-<?php if($remoteSpeakersPos == '') { echo 'top'; } else { echo $remoteSpeakersPos; } ?>" <?php echo $agoraStyle ?>>
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
              <div id="finished-btn" class="room-title">
                <?php if($buttonIcon) { ?>
                  <i id="watch-live-icon" class="fas fa-broadcast-tower"></i>
                <?php } ?>
                <span id="txt-finished" style="display:none"><?php _e('The Live Stream has finished', 'agoraio'); ?></span>
                <span id="txt-waiting"><?php if($channel->type() == 'Communication'){ _e('Waiting for communication connection...', 'agoraio'); } else { _e('Waiting for broadcast connection...', 'agoraio'); }; ?></span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <?php
    if (isset($agora->settings['agora-chat']) && $agora->settings['agora-chat']==='enabled') {
      require_once('parts/chat-fab.php');
    }  ?>

    <?php require_once('parts/raise-hand.php'); ?>

  </section>

  <?php require_once "parts/scripts-common.php" ?>
  <script>
    window.agoraCurrentRole = 'audience';
    window.agoraMode = 'audience';
    window.remoteStreams = {};
    var WAIT_FOR_RECONNECT_TIMEOUT = 15000; // 10 Seconds!

    if(typeof window.roleFromHostToAudience!='undefined'){
      handleOnLoad();
    }
    else{
      window.addEventListener('load', function() {
        /* Check if Raise Hand Request was accepted - on Refresh (using session storage) */
        /* In joinAsHostApprovedUserId, there will be window.userId - that will be 0 for logged-out user, so skipping that as in logged out users every time, a new user id is generated */
        if(sessionStorage.getItem("joinAsHostApprovedUserId")!=0 && sessionStorage.getItem("joinAsHostApprovedUserId") == window.userID){
          joinAsAgoraHost();
        } else {
          handleOnLoad();
        }
      });
    }

    function handleOnLoad(){
      // set log level:
      // -- .DEBUG for dev 
      // -- .NONE for prod
      AgoraRTC.Logger.enableLogUpload();
      window.agoraLogLevel = window.location.href.indexOf('local')>0 ? AgoraRTC.Logger.ERROR : AgoraRTC.Logger.ERROR;
      AgoraRTC.Logger.setLogLevel(window.agoraLogLevel);
      
      // create client, vp8 to work across mobile devices
      window.agoraClient = AgoraRTC.createClient({mode: 'live', codec: 'vp8'});

      window.AGORA_RTM_UTILS.setupRTM(window.agoraAppId, window.channelName);

      jQuery('#fullscreen-expand').click(window.AGORA_UTILS.toggleFullscreen);

      const exitBtn = jQuery('#exit-btn')
      exitBtn.hide();
      exitBtn.click(function() {
        //Object.values(window.remoteStreams).forEach(stream => stream.close());
        Object.keys(window.remoteStreams).forEach(function(key) {
          window.remoteStreams[key].stream.close()
        });
        window.remoteStreams = {};
        window.AGORA_UTILS.agoraLeaveChannel();
        //finishVideoScreen();
      })
      
      // Due to broswer restrictions on auto-playing video, 
      // user must click to init and join channel
      jQuery("#watch-live-btn").click(function(){
        AgoraRTC.Logger.info("user clicked to watch broadcast");

        // init Agora SDK
        window.agoraClient.init(agoraAppId, function () {
          jQuery("#watch-live-overlay").remove();
          jQuery("#screen-zone").css('background', 'none')
          jQuery("#full-screen-video").show();
          AgoraRTC.Logger.info('AgoraRTC client initialized');
          agoraJoinChannel(); // join channel upon successfull init
        }, function (err) {
          AgoraRTC.Logger.error('[ERROR] : AgoraRTC client init failed', err);
        });
      });

      window.AGORA_UTILS.setupAgoraListeners();

    }

    window.AGORA_AUDIENCE = {
      //agoraLeaveChannel: agoraLeaveChannel,
      raiseHandRequestRejected: raiseHandRequestRejected
    };

    function raiseHandRequestRejected(){
      //alert("Your request is rejected");
      showToastMsg('Rejected', "Your raise hand request is rejected.");
      jQuery("#cancelRaiseHand .hand-icon").attr("title", "Raise Hand");
      jQuery("#cancelRaiseHand").attr("id", "raiseHand");
      if(canHandleStateOnRefresh()){
        sessionStorage.removeItem("raisedHandReqUserId");
      }
    }

    // join a channel
    function agoraJoinChannel() {
      const token = window.AGORA_TOKEN_UTILS.agoraGenerateToken();

      // set the role
      window.agoraClient.setClientRole('audience', function() {
        AgoraRTC.Logger.info('Client role set to audience');
      }, function(e) {
        AgoraRTC.Logger.error('setClientRole failed', e);
      });
      
      window.agoraClient.join(token, window.channelName, window.userID, function(uid) {
          AgoraRTC.Logger.info('User ' + uid + ' join channel successfully');
          console.log('User ' + uid + ' join channel successfully')
          window.audienceUserId = uid;
          window.AGORA_RTM_UTILS.joinChannel(uid, function(err){
            if (err) {
              console.error(err)
            }
          });
      }, function(err) {
          AgoraRTC.Logger.error('[ERROR] : join channel failed', err);
      });
    }
  </script>

  <?php require_once "parts/toast.php" ?>

</div>
