
window.screenClient = AgoraRTC.createClient({mode: 'rtc', codec: 'vp8'});
window.screenshareClients = {}; // remote streams from screen shares

window.AGORA_SCREENSHARE_UTILS = {

  toggleScreenShareBtn: function () {
    jQuery('#screen-share-btn').toggleClass('btn-danger');
    jQuery('#screen-share-icon').toggleClass('fa-share-square').toggleClass('fa-times-circle');
  },

  // SCREEN SHARING
  initScreenShare: function (cb) {

    // TODO: Validate window.remoteStreams length !== 0 ?
    if (Object.keys(window.screenshareClients).length>0) {
      cb('Screen sharing in progress', null);
      window.AGORA_UTILS.showErrorMessage('There is another screen sharing session in progress');
      return;
    }

    window.screenClient.init(window.agoraAppId, function (e) {
      AgoraRTC.Logger.info("AgoraRTC screenClient initialized", e);
      window.AGORA_SCREENSHARE_UTILS.joinChannelAsScreenShare(cb);
    }, function (err) {
      AgoraRTC.Logger.error("[ERROR] : AgoraRTC screenClient init failed", err);
      cb(err, null);
    });  
  },

  joinChannelAsScreenShare: function (cb) {
    const userId = null; // window.userID or set to null to auto generate uid on successfull connection
    const successJoin = function(uid) {

      if (window.AGORA_RTM_UTILS) {
        const msg = {
          description: undefined,
          messageType: 'TEXT',
          rawMessage: undefined,
          text: `${uid}: start screen share`
        }
        window.AGORA_RTM_UTILS.sendChannelMessage(msg);
      }

      window.localStreams.screen.id = uid;  // keep track of the uid of the screen stream.
      
      // Create the stream for screen sharing.
      const screenStream = AgoraRTC.createStream({
        streamID: uid,
        audio: false, // Set the audio attribute as false to avoid any echo during the call.
        video: false,
        screen: true, // screen stream
        screenAudio: true,
        mediaSource:  'screen', // Firefox: 'screen', 'application', 'window' (select one)
      });

      screenStream.setScreenProfile(screenVideoProfile); // set the profile of the screen
      screenStream.init(function successInit(){
        AgoraRTC.Logger.info("getScreen successful");
        window.localStreams.screen.stream = screenStream; // keep track of the screen stream


        window.screenClient.publish(screenStream, function (err) {
          AgoraRTC.Logger.error("[ERROR] : publish screen stream error: " + err);
        });

        // access to real MediaStream from browser:
        window.localStreams.screen.stream.stream.getVideoTracks()[0].onended = function() {
          window.AGORA_SCREENSHARE_UTILS.stopScreenShare();
          window.AGORA_SCREENSHARE_UTILS.toggleScreenShareBtn();
          const loaderIcon = jQuery("#screen-share-btn").find('.spinner-border');
          const closeIcon = jQuery('#screen-share-icon');
          loaderIcon.hide();
          closeIcon.show();
        }

        jQuery("#screen-share-btn").prop("disabled", false); // enable button
        window.screenShareActive = true;
        cb(null, true);
      }, function errorInit(err) {
        AgoraRTC.Logger.error("[ERROR] : getScreen failed", err);
        window.localStreams.screen.id = ""; // reset screen stream id
        window.localStreams.screen.stream = {}; // reset the screen stream
        window.screenShareActive = false; // resest screenShare
        cb(err, null);
        // window.AGORA_SCREENSHARE_UTILS.toggleScreenShareBtn(); // toggle the button icon back (will appear disabled)
        if (err && err.info) {
          alert('ScreenShare Error: ' + err.info);
        }
      });
    };
    const failedJoin = function(err) {
      AgoraRTC.Logger.error("[ERROR] : join channel as screen-share failed", err);
      cb(err, null);
    };

    window.AGORA_SCREENSHARE_UTILS.agora_generateAjaxToken(function(err, token) {
      if (err) {
        AgoraRTC.Logger.error("[TOKEN ERROR] : Get Token failed:", err);
        cb(err, null);
        return false;
      }

      window.screenClient.join(token, window.channelName, userId, successJoin, failedJoin);

    });

    window.screenClient.on('stream-published', function (evt) {
      AgoraRTC.Logger.info("Publish screen stream successfully");
      // localStreams.camera.stream.muteVideo(); // disable the local video stream (will send a mute signal)
      // localStreams.camera.stream.stop(); // stop playing the local stream
      
      // TODO: add logic to swap main video feed back from container
      // debugger;
      if (typeof mainStreamId !== 'undefined') {
        remoteStreams[mainStreamId].stop(); // stop the main video stream playback
        
        if (window.AGORA_COMMUNICATION_CLIENT.addRemoteStreamView) {
          window.AGORA_COMMUNICATION_CLIENT.addRemoteStreamView(remoteStreams[mainStreamId]); // send the main video stream to a container
        }
      }
      // localStreams.screen.stream.play('full-screen-video'); // play the screen share as full-screen-video (vortext effect?)
      // jQuery("#video-btn").prop("disabled",true); // disable the video button (as cameara video stream is disabled)
    });
    
    window.screenClient.on('stopScreenSharing', function (evt) {
      AgoraRTC.Logger.info("screen sharing stopped", err);
    });
  },

  stopScreenShare: function (cb) {
    localStreams.screen.stream.muteVideo(); // disable the local video stream (will send a mute signal)
    localStreams.screen.stream.isPlaying() && localStreams.screen.stream.stop(); // stop playing the local stream
    // localStreams.camera.stream.enableVideo(); // enable the camera feed

    var videoContainer = window.agoraMode==='communication' ? 'local-video' : 'full-screen-video';
    localStreams.camera.stream && localStreams.camera.stream.play(videoContainer); // play the camera within the full-screen-video div
    // jQuery("#video-btn").prop("disabled",false);
    window.screenClient.leave(function() {
      AgoraRTC.Logger.info("screen client leaves channel");
      jQuery("#screen-share-btn").prop("disabled", false); // enable button

      window.screenClient.unpublish(localStreams.screen.stream); // unpublish the screen client
      localStreams.screen.stream.close(); // close the screen client stream
      localStreams.screen.id = ""; // reset the screen id
      localStreams.screen.stream = {}; // reset the stream obj
      window.screenShareActive = false; 
      cb && typeof cb==='function' && cb(null, true);
    }, function(err) {
      AgoraRTC.Logger.info("client leave failed ", err); //error handling
      cb && typeof cb==='function' && cb(err, null);
    }); 
  },


  addRemoteScreenshare: function (remoteStream) {
    const streamsContainer = jQuery('#screen-zone');
    streamsContainer.toggleClass('sharescreen');
    
    const streamId = remoteStream.getId();
    console.log('Adding remote screen share:', streamId);

    streamsContainer.append(
      jQuery('<div/>', {'id': streamId + '_container',  'class': 'screenshare-container'}).append(
        jQuery('<div/>', {'id': streamId + '_mute', 'class': 'mute-overlay'}).append(
            jQuery('<i/>', {'class': 'fas fa-microphone-slash'})
        ),
        jQuery('<div/>', {'id': streamId + '_no-video', 'class': 'no-video-overlay text-center'}).append(
          jQuery('<i/>', {'class': 'fas fa-user'})
        ),
        jQuery('<div/>', {'id': 'agora_remote_' + streamId, 'class': 'remote-video'})
      )
    );

    const remoteEl = document.getElementById(streamId + '_container');
    const divWidth = remoteEl.getBoundingClientRect().width;
    remoteEl.style.height = (divWidth / 1.35) + 'px'; // ratio 16:10

    // Play the new screen stream
    remoteStream.play('agora_remote_' + streamId);
    const videoEl = document.getElementById('agora_remote_' + streamId).querySelector('video');
    videoEl.style.objectFit = 'contain';
    videoEl.style.objectPosition = 'top';
  },

  agora_generateAjaxTokenRTM: function (cb, uid) {
    window.AGORA_SCREENSHARE_UTILS.agora_generateAjaxToken(cb, uid, 'RTM')
  },

  agora_generateAjaxToken: function (cb, uid, type) {
    const params = {
      action: 'generate_token', // wp ajax action
      cid: window.channelId,
      uid: uid || 0, // needed to generate a new uid
    };
    if (type) {
      params.type = type;
    }
    window.AGORA_UTILS.agoraApiRequest(ajax_url, params).done(function(data){
      if (data && data.token) {
        cb(null, data.token);
      } else {
        cb('Token not available', null);
      }
    }).fail(function(err){
      console.error(err);
      if(err && err.error) {
        cb(err.error, null);
      } else {
        cb(err.toString(), null);
      }
    })
    
  },
}