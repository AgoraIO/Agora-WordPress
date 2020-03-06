window.AGORA_SCREENSHARE_UTILS = {
  toggleScreenShareBtn: function () {
    jQuery('#screen-share-btn').toggleClass('btn-danger');
    jQuery('#screen-share-icon').toggleClass('fa-share-square').toggleClass('fa-times-circle');
  },

  // SCREEN SHARING
  initScreenShare: function (cb) {
    window.screenClient.init(agoraAppId, function (e) {
      AgoraRTC.Logger.info("AgoraRTC screenClient initialized", e);
      window.AGORA_SCREENSHARE_UTILS.joinChannelAsScreenShare(cb);
      // window.screenShareActive = true;

      // TODO: add logic to swap button
    }, function (err) {
      AgoraRTC.Logger.error("[ERROR] : AgoraRTC screenClient init failed", err);
      cb(err, null);
    });  
  },

  joinChannelAsScreenShare: function (cb) {
    var userId = null; // window.userID or set to null to auto generate uid on successfull connection
    var successJoin = function(uid) {
      localStreams.screen.id = uid;  // keep track of the uid of the screen stream.
      
      // Create the stream for screen sharing.
      var screenStream = AgoraRTC.createStream({
        streamID: uid,
        audio: false, // Set the audio attribute as false to avoid any echo during the call.
        video: false,
        screen: true, // screen stream
        extensionId: 'minllpmhdgpndnkomcoccfekfegnlikg', // Google Chrome:
        mediaSource:  'screen', // Firefox: 'screen', 'application', 'window' (select one)
      });
      screenStream.setScreenProfile(screenVideoProfile); // set the profile of the screen
      screenStream.init(function(){
        AgoraRTC.Logger.info("getScreen successful");
        localStreams.screen.stream = screenStream; // keep track of the screen stream
        window.screenClient.publish(screenStream, function (err) {
          AgoraRTC.Logger.error("[ERROR] : publish screen stream error: " + err);
        });

        jQuery("#screen-share-btn").prop("disabled", false); // enable button
        window.screenShareActive = true;
        cb(null, true);
      }, function (err) {
        AgoraRTC.Logger.error("[ERROR] : getScreen failed", err);
        localStreams.screen.id = ""; // reset screen stream id
        localStreams.screen.stream = {}; // reset the screen stream
        window.screenShareActive = false; // resest screenShare
        cb(err, null);
        window.AGORA_SCREENSHARE_UTILS.toggleScreenShareBtn(); // toggle the button icon back (will appear disabled)
        if (err&& err.info) {
          alert(err.info);
        }
      });
    };
    var failedJoin = function(err) {
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
      localStreams.camera.stream.muteVideo(); // disable the local video stream (will send a mute signal)
      localStreams.camera.stream.stop(); // stop playing the local stream
      // TODO: add logic to swap main video feed back from container
      if (typeof mainStreamId !== 'undefined') {
        remoteStreams[mainStreamId].stop(); // stop the main video stream playback
        
        if (window.AGORA_COMMUNICATION_CLIENT.addRemoteStreamMiniView) {
          window.AGORA_COMMUNICATION_CLIENT.addRemoteStreamMiniView(remoteStreams[mainStreamId]); // send the main video stream to a container
        }
      }
      // localStreams.screen.stream.play('full-screen-video'); // play the screen share as full-screen-video (vortext effect?)
      jQuery("#video-btn").prop("disabled",true); // disable the video button (as cameara video stream is disabled)
    });
    
    window.screenClient.on('stopScreenSharing', function (evt) {
      AgoraRTC.Logger.info("screen sharing stopped", err);
    });

  },

  stopScreenShare: function (cb) {
    localStreams.screen.stream.muteVideo(); // disable the local video stream (will send a mute signal)
    localStreams.screen.stream.stop(); // stop playing the local stream
    localStreams.camera.stream.enableVideo(); // enable the camera feed

    var videoContainer = window.agoraMode==='communication' ? 'local-video' : 'full-screen-video';
    localStreams.camera.stream.play(videoContainer); // play the camera within the full-screen-video div
    jQuery("#video-btn").prop("disabled",false);
    window.screenClient.leave(function() {
      window.screenShareActive = false; 
      AgoraRTC.Logger.info("screen client leaves channel");
      jQuery("#screen-share-btn").prop("disabled", false); // enable button
      window.screenClient.unpublish(localStreams.screen.stream); // unpublish the screen client
      localStreams.screen.stream.close(); // close the screen client stream
      localStreams.screen.id = ""; // reset the screen id
      localStreams.screen.stream = {}; // reset the stream obj
      cb(null, true);
    }, function(err) {
      AgoraRTC.Logger.info("client leave failed ", err); //error handling
      cb(err, null);
    }); 
  },

  agora_generateAjaxToken: function (cb) {
    var params = {
      action: 'generate_token', // wp ajax action
      cid: window.channelId,
      uid: 0, // needed to generate a new uid
    };
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
    
  }
}