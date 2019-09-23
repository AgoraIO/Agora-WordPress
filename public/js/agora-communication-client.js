/*
 * JS Interface for Agora.io SDK
 */
// create client instances for camera (client) and screen share (screenClient)
var client = AgoraRTC.createClient({mode: 'rtc', codec: 'vp8'}); 
var screenClient = AgoraRTC.createClient({mode: 'rtc', codec: 'vp8'}); 

// stream references (keep track of active streams) 
var remoteStreams = {}; // remote streams obj struct [id : stream] 

var localStreams = {
  camera: {
    id: "",
    stream: {}
  },
  screen: {
    id: "",
    stream: {}
  }
};

var mainStreamId; // reference to main stream
var screenShareActive = false; // flag for screen share 

function initClientAndJoinChannel(agoraAppId, channelName) {
  // init Agora SDK
  client.init(agoraAppId, function () {
    AgoraRTC.Logger.info("AgoraRTC client initialized");
    joinChannel(channelName); // join channel upon successfull init
  }, function (err) {
    AgoraRTC.Logger.error("[ERROR] : AgoraRTC client init failed", err);
  });
}


client.on('stream-published', function (evt) {
  AgoraRTC.Logger.info("Publish local stream successfully");
});

// connect remote streams
client.on('stream-added', function (evt) {
  var stream = evt.stream;
  var streamId = stream.getId();
  AgoraRTC.Logger.info("new stream added: " + streamId);
  // Check if the stream is local
  if (streamId != localStreams.screen.id) {
    AgoraRTC.Logger.info('subscribe to remote stream:' + streamId);
    // Subscribe to the stream.
    client.subscribe(stream, function (err) {
      AgoraRTC.Logger.error("[ERROR] : subscribe stream failed", err);
    });
  }
});

client.on('stream-subscribed', function (evt) {
  var remoteStream = evt.stream;
  var remoteId = remoteStream.getId();
  remoteStreams[remoteId] = remoteStream;
  AgoraRTC.Logger.info("Subscribe remote stream successfully: " + remoteId);
  if( jQuery('#full-screen-video').is(':empty') ) { 
    mainStreamId = remoteId;
    remoteStream.play('full-screen-video');
  } else {
    addRemoteStreamMiniView(remoteStream);
  }
});

// remove the remote-container when a user leaves the channel
client.on("peer-leave", function(evt) {
  var streamId = evt.stream.getId(); // the the stream id
  if(remoteStreams[streamId] != undefined) {
    remoteStreams[streamId].stop(); // stop playing the feed
    delete remoteStreams[streamId]; // remove stream from list
    if (streamId == mainStreamId) {
      var streamIds = Object.keys(remoteStreams);
      var randomId = streamIds[Math.floor(Math.random()*streamIds.length)]; // select from the remaining streams
      remoteStreams[randomId].stop(); // stop the stream's existing playback
      var remoteContainerID = '#' + randomId + '_container';
      jQuery(remoteContainerID).empty().remove(); // remove the stream's miniView container
      remoteStreams[randomId].play('full-screen-video'); // play the random stream as the main stream
      mainStreamId = randomId; // set the new main remote stream
    } else {
      var remoteContainerID = '#' + streamId + '_container';
      jQuery(remoteContainerID).empty().remove(); // 
    }
  }
});

// show mute icon whenever a remote has muted their mic
client.on("mute-audio", function (evt) {
  toggleVisibility('#' + evt.uid + '_mute', true);
});

client.on("unmute-audio", function (evt) {
  toggleVisibility('#' + evt.uid + '_mute', false);
});

// show user icon whenever a remote has disabled their video
client.on("mute-video", function (evt) {
  var remoteId = evt.uid;
  // if the main user stops their video select a random user from the list
  if (remoteId != mainStreamId) {
    // if not the main vidiel then show the user icon
    toggleVisibility('#' + remoteId + '_no-video', true);
  }
});

client.on("unmute-video", function (evt) {
  toggleVisibility('#' + evt.uid + '_no-video', false);
});

// join a channel
function joinChannel(channelName) {
  var token = generateToken();
  var userID = null; // set to null to auto generate uid on successfull connection
  client.join(token, channelName, userID, function(uid) {
      AgoraRTC.Logger.info("User " + uid + " join channel successfully");
      createCameraStream(uid);
      localStreams.camera.id = uid; // keep track of the stream uid 
  }, function(err) {
      AgoraRTC.Logger.error("[ERROR] : join channel failed", err);
  });
}

// video streams for channel
function createCameraStream(uid) {
  var localStream = AgoraRTC.createStream({
    streamID: uid,
    audio: true,
    video: true,
    screen: false
  });
  localStream.setVideoProfile(window.cameraVideoProfile);
  localStream.init(function() {
    AgoraRTC.Logger.info("getUserMedia successfully");
    // TODO: add check for other streams. play local stream full size if alone in channel
    localStream.play('local-video'); // play the given stream within the local-video div

    // publish local stream
    client.publish(localStream, function (err) {
      AgoraRTC.Logger.error("[ERROR] : publish local stream error: " + err);
    });
  
    enableUiControls(localStream); // move after testing
    localStreams.camera.stream = localStream; // keep track of the camera stream for later
  }, function (err) {
    AgoraRTC.Logger.error("[ERROR] : getUserMedia failed", err);
  });
}

// SCREEN SHARING
function initScreenShare() {
  screenClient.init(agoraAppId, function () {
    AgoraRTC.Logger.info("AgoraRTC screenClient initialized");
    joinChannelAsScreenShare();
    screenShareActive = true;
    // TODO: add logic to swap button
  }, function (err) {
    AgoraRTC.Logger.error("[ERROR] : AgoraRTC screenClient init failed", err);
  });  
}

function joinChannelAsScreenShare() {
  var token = generateToken();
  var userID = null; // set to null to auto generate uid on successfull connection
  screenClient.join(token, channelName, userID, function(uid) { 
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
      screenClient.publish(screenStream, function (err) {
        AgoraRTC.Logger.errorerror("[ERROR] : publish screen stream error: " + err);
      });
    }, function (err) {
      AgoraRTC.Logger.error("[ERROR] : getScreen failed", err);
      localStreams.screen.id = ""; // reset screen stream id
      localStreams.screen.stream = {}; // reset the screen stream
      screenShareActive = false; // resest screenShare
      toggleScreenShareBtn(); // toggle the button icon back (will appear disabled)
    });
  }, function(err) {
    AgoraRTC.Logger.error("[ERROR] : join channel as screen-share failed", err);
  });

  screenClient.on('stream-published', function (evt) {
    AgoraRTC.Logger.info("Publish screen stream successfully");
    localStreams.camera.stream.disableVideo(); // disable the local video stream (will send a mute signal)
    localStreams.camera.stream.stop(); // stop playing the local stream
    // TODO: add logic to swap main video feed back from container
    remoteStreams[mainStreamId].stop(); // stop the main video stream playback
    addRemoteStreamMiniView(remoteStreams[mainStreamId]); // send the main video stream to a container
    // localStreams.screen.stream.play('full-screen-video'); // play the screen share as full-screen-video (vortext effect?)
    jQuery("#video-btn").prop("disabled",true); // disable the video button (as cameara video stream is disabled)
  });
  
  screenClient.on('stopScreenSharing', function (evt) {
    AgoraRTC.Logger.info("screen sharing stopped", err);
  });
}

function stopScreenShare() {
  localStreams.screen.stream.disableVideo(); // disable the local video stream (will send a mute signal)
  localStreams.screen.stream.stop(); // stop playing the local stream
  localStreams.camera.stream.enableVideo(); // enable the camera feed
  localStreams.camera.stream.play('local-video'); // play the camera within the full-screen-video div
  jQuery("#video-btn").prop("disabled",false);
  screenClient.leave(function() {
    screenShareActive = false; 
    AgoraRTC.Logger.info("screen client leaves channel");
    jQuery("#screen-share-btn").prop("disabled",false); // enable button
    screenClient.unpublish(localStreams.screen.stream); // unpublish the screen client
    localStreams.screen.stream.close(); // close the screen client stream
    localStreams.screen.id = ""; // reset the screen id
    localStreams.screen.stream = {}; // reset the stream obj
  }, function(err) {
    AgoraRTC.Logger.info("client leave failed ", err); //error handling
  }); 
}

// REMOTE STREAMS UI
function addRemoteStreamMiniView(remoteStream){
  var streamId = remoteStream.getId();
  // append the remote stream template to #remote-streams
  jQuery('#remote-streams').append(
    jQuery('<div/>', {'id': streamId + '_container',  'class': 'remote-stream-container col'}).append(
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

  var containerId = '#' + streamId + '_container';
  jQuery(containerId).dblclick(function() {
    // play selected container as full screen - swap out current full screen stream
    remoteStreams[mainStreamId].stop(); // stop the main video stream playback
    addRemoteStreamMiniView(remoteStreams[mainStreamId]); // send the main video stream to a container
    jQuery(containerId).empty().remove(); // remove the stream's miniView container
    remoteStreams[streamId].stop() // stop the container's video stream playback
    remoteStreams[streamId].play('full-screen-video'); // play the remote stream as the full screen video
    mainStreamId = streamId; // set the container stream id as the new main stream id
  });
}

function leaveChannel() {
  
  if(screenShareActive) {
    stopScreenShare();
  }

  client.leave(function() {
    AgoraRTC.Logger.info("client leaves channel");
    localStreams.camera.stream.stop() // stop the camera stream playback
    client.unpublish(localStreams.camera.stream); // unpublish the camera stream
    localStreams.camera.stream.close(); // clean up and close the camera stream
    jQuery("#remote-streams").empty() // clean up the remote feeds
    //disable the UI elements
    jQuery("#mic-btn").prop("disabled", true);
    jQuery("#video-btn").prop("disabled", true);
    jQuery("#screen-share-btn").prop("disabled", true);
    jQuery("#exit-btn").prop("disabled", true);
    // hide the mute/no-video overlays
    toggleVisibility("#mute-overlay", false); 
    toggleVisibility("#no-local-video", false);
    
    // show the modal overlay to join
    // jQuery("#modalForm").modal("show"); 
  }, function(err) {
    AgoraRTC.Logger.error("client leave failed ", err); //error handling
  });
}
