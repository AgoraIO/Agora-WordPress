
function toggleScreenShareBtn() {
  jQuery('#screen-share-btn').toggleClass('btn-danger');
  jQuery('#screen-share-icon').toggleClass('fa-share-square').toggleClass('fa-times-circle');
}

// SCREEN SHARING
function initScreenShare() {
  window.screenClient.init(agoraAppId, function () {
    AgoraRTC.Logger.info("AgoraRTC screenClient initialized");
    joinChannelAsScreenShare();
    window.screenShareActive = true;
    // TODO: add logic to swap button
  }, function (err) {
    AgoraRTC.Logger.error("[ERROR] : AgoraRTC screenClient init failed", err);
  });  
}

function joinChannelAsScreenShare() {
  var token = generateToken();
  var userId = null; // set to null to auto generate uid on successfull connection
  window.screenClient.join(token, window.channelName, userId, function(uid) { 
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
      jQuery("#screen-share-btn").prop("disabled",false); // enable button
      window.screenClient.publish(screenStream, function (err) {
        AgoraRTC.Logger.errorerror("[ERROR] : publish screen stream error: " + err);
      });
    }, function (err) {
      AgoraRTC.Logger.error("[ERROR] : getScreen failed", err);
      localStreams.screen.id = ""; // reset screen stream id
      localStreams.screen.stream = {}; // reset the screen stream
      window.screenShareActive = false; // resest screenShare
      toggleScreenShareBtn(); // toggle the button icon back (will appear disabled)
    });
  }, function(err) {
    AgoraRTC.Logger.error("[ERROR] : join channel as screen-share failed", err);
  });

  window.screenClient.on('stream-published', function (evt) {
    AgoraRTC.Logger.info("Publish screen stream successfully");
    localStreams.camera.stream.disableVideo(); // disable the local video stream (will send a mute signal)
    localStreams.camera.stream.stop(); // stop playing the local stream
    // TODO: add logic to swap main video feed back from container
    remoteStreams[mainStreamId].stop(); // stop the main video stream playback
    
    if (addRemoteStreamMiniView) {
      addRemoteStreamMiniView(remoteStreams[mainStreamId]); // send the main video stream to a container
    }
    // localStreams.screen.stream.play('full-screen-video'); // play the screen share as full-screen-video (vortext effect?)
    jQuery("#video-btn").prop("disabled",true); // disable the video button (as cameara video stream is disabled)
  });
  
  window.screenClient.on('stopScreenSharing', function (evt) {
    AgoraRTC.Logger.info("screen sharing stopped", err);
  });
}

function stopScreenShare() {
  localStreams.screen.stream.disableVideo(); // disable the local video stream (will send a mute signal)
  localStreams.screen.stream.stop(); // stop playing the local stream
  localStreams.camera.stream.enableVideo(); // enable the camera feed
  localStreams.camera.stream.play('local-video'); // play the camera within the full-screen-video div
  jQuery("#video-btn").prop("disabled",false);
  window.screenClient.leave(function() {
    window.screenShareActive = false; 
    AgoraRTC.Logger.info("screen client leaves channel");
    jQuery("#screen-share-btn").prop("disabled",false); // enable button
    window.screenClient.unpublish(localStreams.screen.stream); // unpublish the screen client
    localStreams.screen.stream.close(); // close the screen client stream
    localStreams.screen.id = ""; // reset the screen id
    localStreams.screen.stream = {}; // reset the stream obj
  }, function(err) {
    AgoraRTC.Logger.info("client leave failed ", err); //error handling
  }); 
}