
window.AGORA_COMMUNICATION_UI = {
// UI buttons
  enableUiControls: function (localStream) {

    jQuery('#rejoin-btn').click(window.AGORA_COMMUNICATION_UI.rejoinChannel);

    // keyboard listeners
    function keyboardListeners(e) {
      switch (e.key) {
        case "m":
          console.log("quick toggle the mic");
          window.AGORA_UI_UTILS.toggleMic(localStream);
          break;
        case "v":
          console.log("quick toggle the video");
          window.AGORA_UI_UTILS.toggleVideo(localStream);
          break; 
        case "s":
          console.log("initializing screen share");
          toggleScreenShareBtn(); // set screen share button icon
          jQuery("#screen-share-btn").prop("disabled",true); // disable the button on click
          if(screenShareActive){
            stopScreenShare();
          } else {
            initScreenShare(); 
          }
          break;
        case "q":
          console.log("so sad to see you quit the channel");
          window.AGORA_UTILS.agoraLeaveChannel(); 
          break;   
        default:  // do nothing
      }

      // (for testing) 
      if(e.key === "r") { 
        // window.history.back(); // quick reset
      }
    };
    // jQuery(document).keypress(keyboardListeners);
  },

  logCameraDevices: function () {
    console.log("Checking for Camera Devices.....")
    AgoraRTC.getDevices (function(devices) {
      var devCount = devices.length;
      if (devCount>0) {
        var id = devices[0].deviceId;
        // console.log('Device:', devices[0])
      }
    });

    agoraClient.getCameras(function(cameras) {
      var devCount = cameras.length;
      var id = cameras[0].deviceId;
      // console.log("getCameras: " + JSON.stringify(cameras));
    });
  },


  rejoinChannel: function () {
    var thisBtn = jQuery(this);
    if(!thisBtn.prop('disabled')) {
      thisBtn.prop("disabled", true);
      thisBtn.find('.spinner-border').show();
      // joinChannel(window.channelName);
      if (jQuery("#mic-icon").hasClass('fa-microphone-slash')) {
        jQuery("#mic-icon").toggleClass('fa-microphone').toggleClass('fa-microphone-slash');
        window.AGORA_UTILS.toggleVisibility("#mute-overlay", false); // hide the muted mic icon
      }
      
      if (jQuery("#video-icon").hasClass('fa-video-slash')) {
        jQuery("#video-icon").toggleClass('fa-video').toggleClass('fa-video-slash'); // toggle the video icon
        window.AGORA_UTILS.toggleVisibility("#no-local-video", false); // hide the user icon when video is enabled
      } 
      window.AGORA_UTILS.agoraJoinChannel(window.channelName);
    }
  },

  /* Function to check if user can join as a host (If user is not in the list of broadcaster users and total remote streams is already equeal to max hosts allowed, then, user will join as audience) */
  canJoinAsHost: function(){
    if(window.joinAsHost == 0 && window.max_host_users_limit!=''){
      
      let totalRemoteStreams = Object.keys(window.remoteStreams).length;
      
      /* Exclude Screen Share Streams from count */
      let count = Object.keys(window.remoteStreams).filter(k => k in window.screenshareClients).length;
      totalRemoteStreams = totalRemoteStreams-count;

      /* Exclude Host users streams from count */
      let hostsCount = Object.keys(window.remoteStreams).filter(k => k in window.host_users).length;
      totalRemoteStreams = totalRemoteStreams-hostsCount;

      console.log("hlwtotalRemoteStreams", totalRemoteStreams)

      if(totalRemoteStreams>=window.max_host_users_limit){
        return false;
      } else {
        return true;
      }
    } else {
      return true;
    }
  },

  joinAsAudience: async function(){
    //setTimeout(async() => {
      
      await window.AGORA_UTILS.agoraLeaveChannel();
      var params = {
        action: 'load_audience_view', // wp ajax action
        channel_id: window.channelId,
        page_title: page_title
      };
    
      /* Remove Previous RTM Event Listeners when joining from audience to host */
      window.removeEventListener('agora.rtm_init', loadChatApp);
      window.removeEventListener('agora.rtmMessageFromChannel', receiveRTMMessage);
    
      /* Remove Previous Files of audience */
      if(jQuery("script#wp-agora-io-chat-js").length>0){
        jQuery("script#wp-agora-io-chat-js").remove();
      }
    
      if(jQuery("script#AgoraCommunicationClient-js").length>0){
        jQuery("script#AgoraCommunicationClient-js").remove();
      }

      if(jQuery("script#wp-agora-raise-hand-js").length>0){
        jQuery("script#wp-agora-raise-hand-js").remove();
      }
    
      //jQuery("link#wp-agora-io-chat-fab-css").remove();
    
      window.AGORA_UTILS.agoraApiRequest(ajax_url, params).done(function(res) {
        console.log("afterAjaxSuccess")
    
        let mainElm = jQuery('#agora-root').parent();
        jQuery('#agora-root').remove();
        mainElm.html(res);
        appendDivWithAllStreamHiddenInGhostMode();
        jQuery("#raiseHand").remove();
        apply_global_colors();
    
      }).fail(function(err) {
        console.error('API Error:', err.responseJSON ? err.responseJSON.errors : err);
      })
    //}, 2000);
  }

}