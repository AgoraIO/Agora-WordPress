window.AGORA_BROADCAST_UI = {
// UI buttons
  enableUiControls: function () {

    jQuery("#mic-btn").prop("disabled", false);
    jQuery("#video-btn").prop("disabled", false);
    jQuery("#exit-btn").prop("disabled", false);
    jQuery("#add-rtmp-btn").prop("disabled", false);

    jQuery("#mic-btn").click(function(){
      window.AGORA_BROADCAST_UI.toggleMic();
    });

    jQuery("#video-btn").click(function(){
      window.AGORA_BROADCAST_UI.toggleVideo();
    });

    jQuery("#cloud-recording-btn").click(function(){
      window.AGORA_BROADCAST_UI.toggleRecording();
    });

    jQuery("#exit-btn").click(function(){
      console.log("so sad to see you leave the channel");
      window.AGORA_BROADCAST_CLIENT.agoraLeaveChannel(); 
    });

    jQuery("#start-RTMP-broadcast").click(function(){
      var formValid = document.getElementById('rtmp-config').checkValidity();
      var errorEl = jQuery('#rtmp-error-msg');
      if (!formValid) {
        errorEl.show();
        return;
      } else {
        errorEl.hide();
      }

      window.AGORA_BROADCAST_CLIENT.startLiveTranscoding();
      jQuery('#addRtmpConfigModal').modal('toggle');
      // jQuery('#input_rtmp_url').val('');
    });

    jQuery("#add-external-stream").click(function(){
      var formValid = document.getElementById('external-inject-config').checkValidity();
      var errorEl = jQuery('#external-url-error');
      if (!formValid) {
        errorEl.show();
        return;
      } else {
        errorEl.hide();
      }
      // 
      window.AGORA_BROADCAST_CLIENT.addExternalSource();
      jQuery('#add-external-source-modal').modal('toggle');
    });

    jQuery("#screen-share-btn").click(function(){
      window.AGORA_SCREENSHARE_UTILS.toggleScreenShareBtn(); // set screen share button icon
      var loaderIcon = jQuery(this).find('.spinner-border');
      var closeIcon = jQuery('#screen-share-icon');
      loaderIcon.show();
      closeIcon.hide();

      var toggleLoader = function(err, next) {
        loaderIcon.hide();
        closeIcon.show();
        if (err) {
          window.screenShareActive = false;
          window.AGORA_SCREENSHARE_UTILS.toggleScreenShareBtn();
        }
        jQuery("#screen-share-btn").prop("disabled", false);
      }

      jQuery("#screen-share-btn").prop("disabled", true); // disable the button on click
      if(window.screenShareActive){
        window.AGORA_SCREENSHARE_UTILS.stopScreenShare(toggleLoader);
      } else {
        window.AGORA_SCREENSHARE_UTILS.initScreenShare(toggleLoader);
      }
    });

    // keyboard listeners 
    jQuery(document).keypress(function(e) {
      // ignore keyboard events when the modals are open
      if ((jQuery("#addRtmpUrlModal").data('bs.modal') || {})._isShown ||
          (jQuery("#addRtmpConfigModal").data('bs.modal') || {})._isShown){
        return;
      }

      switch (e.key) {
        case "m":
          console.log("squick toggle the mic");
          window.AGORA_BROADCAST_UI.toggleMic();
          break;
        case "v":
          console.log("quick toggle the video");
          window.AGORA_BROADCAST_UI.toggleVideo();
          break; 
        case "q":
          console.log("so sad to see you quit the channel");
          window.AGORA_BROADCAST_CLIENT.agoraLeaveChannel(); 
          break;   
        default:  // do nothing
      }
    });
  },

  toggleMic: function () {
    window.AGORA_UTILS.toggleBtn(jQuery("#mic-btn")); // toggle button colors
    window.AGORA_UTILS.toggleBtn(jQuery("#mic-dropdown"));
    jQuery("#mic-icon").toggleClass('fa-microphone').toggleClass('fa-microphone-slash'); // toggle the mic icon
    if (jQuery("#mic-icon").hasClass('fa-microphone')) {
      window.localStreams.camera.stream.unmuteAudio(); // enable the local mic
    } else {
      window.localStreams.camera.stream.muteAudio(); // mute the local mic
    }
  },

  toggleVideo: function () {
    window.AGORA_UTILS.toggleBtn(jQuery("#video-btn")); // toggle button colors
    window.AGORA_UTILS.toggleBtn(jQuery("#cam-dropdown"));
    if (jQuery("#video-icon").hasClass('fa-video')) {
      window.localStreams.camera.stream.muteVideo(); // enable the local video
      // console.log("muteVideo");
    } else {
      window.localStreams.camera.stream.unmuteVideo(); // disable the local video
      // console.log("unMuteVideo");
    }
    jQuery("#video-icon").toggleClass('fa-video').toggleClass('fa-video-slash'); // toggle the video icon
  },

  toggleRecording: function () {
    if (window.loadingRecord) {
      return false;
    }

    var btn = jQuery("#cloud-recording-btn");
    if (btn.hasClass('start-rec')) {
      window.loadingRecord = true;
      btn.removeClass('start-rec').addClass('load-rec').attr('title', 'Stop Recording');
      console.log("Starting rec...");
      window.AGORA_BROADCAST_UI.startVideoRecording(function(err, res) {
        if (res) {
          btn.removeClass('load-rec').addClass('stop-rec');
        } else {
          btn.removeClass('load-rec').addClass('start-rec').attr('title', 'Start Recording');
        }
        window.loadingRecord = false;
      });
    } else {
      console.log("Stoping rec...");
      window.AGORA_BROADCAST_UI.stopVideoRecording(function(err, res) {
        if (err) {
          console.error(err);
        } else {
          if(!res.errors) {
            console.log(res);
            btn.removeClass('stop-rec').addClass('start-rec').attr('title', 'Start Recording');
          } else {
            console.error(res.errors);
          }
        }
      })
    }
  },

  calculateVideoScreenSize: function () {
    var container = jQuery('#full-screen-video');
    console.log('Video SIZE:', container.outerWidth());
    var size = window.AGORA_BROADCAST_UI.getSizeFromVideoProfile();

    // https://math.stackexchange.com/a/180805
    var newHeight = container.outerWidth() * size.height / size.width;
    container.outerHeight(newHeight);
  },

  getSizeFromVideoProfile: function () {
    // https://docs.agora.io/en/Interactive%20Broadcast/videoProfile_web?platform=Web#video-profile-table
    switch(window.cameraVideoProfile) {
      case '480p_8':
      case '480p_9': return { width: 848, height: 480 };
      case '720p':
      case '720p_1':
      case '720p_2':
      case '720p_3': return { width: 1280, height: 720 };
      case '720p_6': return { width: 960, height: 720 };
      case '1080p':
      case '1080p_1':
      case '1080p_2':
      case '1080p_3':
      case '1080p_5': return { width: 1920, height: 1080 };
    }
  },

  startVideoRecording: function (cb) {
    var params = {
      action: 'cloud_record', // wp ajax action
      sdk_action: 'start-recording',
      cid: window.channelId,
      cname: window.channelName,
      uid: window.userID,
      token: window.agoraToken
    };
    window.AGORA_UTILS.agoraApiRequest(ajax_url, params).done(function(res) {
      // var startRecordURL = agoraAPI + window.agoraAppId + '/cloud_recording/resourceid/' + res.resourceId + '/mode/mix/start';
      console.log(res);
      if (res && res.sid) {
        window.resourceId = res.resourceId;
        window.recordingId = res.sid;
        window.uid = res.uid;

        setTimeout(function() {
          // window.resourceId = null;
        }, 1000*60*5); // Agora DOCS: The resource ID is valid for five minutes.
        cb(null, res);
      } else {
        cb(res, null);
      }
    }).fail(function(err) {
      if (err.responseJSON) {
        console.error('API Error:', err.responseJSON.errors);
      }
      cb(err, null);
    })
  },


  stopVideoRecording: function (cb) {
    var params = {
      action: 'cloud_record', // wp ajax action
      sdk_action: 'stop-recording',
      cid: window.channelId,
      cname: window.channelName,
      uid: window.uid,
      resourceId: window.resourceId,
      recordingId: window.recordingId
    };
    window.AGORA_UTILS.agoraApiRequest(ajax_url, params).done(function(res) {
      // var startRecordURL = agoraAPI + window.agoraAppId + '/cloud_recording/resourceid/' + res.resourceId + '/mode/mix/start';
      console.log('Stop:', res);
      window.recording = res.serverResponse;
      cb(null, res);

    }).fail(function(err) {
      console.error('API Error:', err.responseJSON.errors);
      cb(err, null);
    })
  },


  queryVideoRecording: function () {
    var params = {
      action: 'cloud_record', // wp ajax action
      sdk_action: 'query-recording',
      cid: window.channelId,
      cname: window.channelName,
      uid: window.uid,
      resourceId: window.resourceId,
      recordingId: window.recordingId
    };
    window.AGORA_UTILS.agoraApiRequest(ajax_url, params).done(function(res) {
      console.log('Query:', res);

    }).fail(function(err) {
      console.error('API Error:',err);
    })
  }
}