<?php $current_user = wp_get_current_user(); ?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="agora agora-communication">
  <div class="container-fluid p-0">
    <div id="main-container" class="controls-top">
      <div id="full-screen-video">
        <div id="video-canvas"></div>
        <div id="screen-share-btn-container" class="col-2 float-right text-right mt-2">
          <button id="screen-share-btn"  type="button" class="btn btn-xs">
            <i id="screen-share-icon" class="fas fa-share-square"></i>
          </button>
        </div>
        <div id="buttons-container" class="row justify-content-center mt-3">
          <div class="col-md-2 text-center">
            <button id="mic-btn" type="button" class="btn btn-block btn-dark btn-xs">
              <i id="mic-icon" class="fas fa-microphone"></i>
            </button>
          </div>
          <div class="col-md-2 text-center">
            <button id="video-btn"  type="button" class="btn btn-block btn-dark btn-xs">
              <i id="video-icon" class="fas fa-video"></i>
            </button>
          </div>
          <div class="col-md-2 text-center">
            <button id="exit-btn"  type="button" class="btn btn-block btn-danger btn-xs">
              <i id="exit-icon" class="fas fa-phone-slash"></i>
            </button>
          </div>
        </div>
        <div id="rejoin-container" class="rejoin-container" style="display: none">
          <button id="rejoin-btn" class="btn btn-primary btn-lg" type="button">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <?php _e('Rejoin to this channel', 'agoraio'); ?>
          </button>
        </div>
        <div id="lower-video-bar" class="row mb-0">
          <div id="remote-streams-container" class="container col-9 ml-1">
            <div id="remote-streams" class="row">
              <!-- insert remote streams dynamically -->
            </div>
          </div>
          <div id="local-stream-container" class="col p-0">
            <div id="mute-overlay" class="col">
              <i id="mic-icon" class="fas fa-microphone-slash"></i>
            </div>
            <div id="no-local-video" class="col text-center">
              <i id="user-icon" class="fas fa-user"></i>
            </div>
            <div id="local-video" class="col p-0"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    // video profile settings
    window.cameraVideoProfile = '<?php echo $instance['videoprofile'] ?>'; // 640x480 @ 30fps & 750kbs
    window.screenVideoProfile = '<?php echo $instance['screenprofile'] ?>';
    window.addEventListener('load', function() {
      window.agoraAppId = '<?php echo $agora->settings['appId'] ?>'; // set app id
      window.channelName = '<?php echo $channel->title() ?>'; // set channel name
      window.userID = <?php echo $current_user->ID; ?>;
      calculateVideoScreenSize();
      initClientAndJoinChannel(window.agoraAppId, window.channelName);
    });

    function rejoinChannel() {
      var thisBtn = jQuery(this);
      if(!thisBtn.prop('disabled')) {
        joinChannel('<?php echo $channel->title() ?>');
        thisBtn.prop("disabled", true);
        thisBtn.find('.spinner-border').show();
      }
    }


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
        echo '"'.RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpireTs). '"';
      } else {
        echo 'null';
      }
      ?>;
    }
  </script>
</div>