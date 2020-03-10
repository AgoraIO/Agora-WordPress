/*
 * JS Interface for Agora.io SDK
 */
// create client instances for camera (client) and screen share (screenClient)
var agoraClient = AgoraRTC.createClient({mode: 'rtc', codec: 'vp8'}); 
window.screenClient = AgoraRTC.createClient({mode: 'rtc', codec: 'vp8'}); 

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

window.AGORA_COMMUNICATION_CLIENT = {
  initClientAndJoinChannel: initClientAndJoinChannel,
  agoraJoinChannel: agoraJoinChannel,
  addRemoteStreamMiniView: addRemoteStreamMiniView,
  agoraLeaveChannel: agoraLeaveChannel
};

function initClientAndJoinChannel(agoraAppId, channelName) {
  // init Agora SDK
  agoraClient.init(agoraAppId, function () {
    AgoraRTC.Logger.info("AgoraRTC client initialized");
    agoraJoinChannel(channelName); // join channel upon successfull init
  }, function (err) {
    AgoraRTC.Logger.error("[ERROR] : AgoraRTC client init failed", err);
  });
}


agoraClient.on('stream-published', function (evt) {
  AgoraRTC.Logger.info("Publish local stream successfully");
});

// connect remote streams
agoraClient.on('stream-added', function (evt) {
  var stream = evt.stream;
  var streamId = stream.getId();
  // AgoraRTC.Logger.info("new stream added: " + streamId);
  // Check if the stream is local
  if (streamId != localStreams.screen.id) {
    AgoraRTC.Logger.info('subscribe to remote stream:' + streamId);
    // Subscribe to the stream.
    agoraClient.subscribe(stream, function (err) {
      AgoraRTC.Logger.error("[ERROR] : subscribe stream failed", err);
    });
  }
});

agoraClient.on('stream-subscribed', function (evt) {
  var remoteStream = evt.stream;
  var remoteId = remoteStream.getId();
  remoteStreams[remoteId] = remoteStream;
  // console.log('Stream subscribed:', remoteId);
  const callbackRemoteStreams = function() {
    AgoraRTC.Logger.info("Subscribe remote stream successfully: " + remoteId);
    if( jQuery('#video-canvas').is(':empty') ) { 
      mainStreamId = remoteId;
      remoteStream.play('video-canvas');
    } else {
      addRemoteStreamMiniView(remoteStream);
    }
  }
  
  const avatarsSlider = jQuery('#slick-avatars');
  if (avatarsSlider.length>0) {
    window.AGORA_UTILS.agora_getUserAvatar(remoteId, function(gravatar) {
      // console.log('callback gravatar:', gravatar);
      const url = gravatar.avatar.url;
      // const index = remoteId;
      const template = '<div id="uid-'+remoteId+'"><div class="avatar-circle"><img src="'+url+'" alt="gravatar" /></div></div>';
      jQuery('#slick-avatars').slick('slickAdd', template);

      callbackRemoteStreams();
    });
  } else {
    callbackRemoteStreams();
  }

});

agoraClient.on('stream-removed', function(evt) {
  console.log('REMOVED: ', evt.uid);
})

// remove the remote-container when a user leaves the channel
agoraClient.on("peer-leave", function(evt) {
  if (!evt || !evt.stream) {
    console.error('Stream undefined cannot be removed', evt);
    return false;
  }
  console.log('peer-leave:', evt);
  var streamId = evt.stream.getId(); // the the stream id
  jQuery('#uid-'+streamId).remove();

  if(remoteStreams[streamId] !== undefined) {
    remoteStreams[streamId].stop(); // stop playing the feed
    delete remoteStreams[streamId]; // remove stream from list
    if (streamId == mainStreamId) {
      var streamIds = Object.keys(remoteStreams);
      var randomId = streamIds[Math.floor(Math.random()*streamIds.length)]; // select from the remaining streams
      if (remoteStreams[randomId]) {
        remoteStreams[randomId].stop(); // stop the stream's existing playback
        var remoteContainerID = '#' + randomId + '_container';
        jQuery(remoteContainerID).empty().remove(); // remove the stream's miniView container
        remoteStreams[randomId].play('video-canvas'); // play the random stream as the main stream
        mainStreamId = randomId; // set the new main remote stream
      }
    } else {
      var remoteContainerID = '#' + streamId + '_container';
      jQuery(remoteContainerID).empty().remove(); // 
    }
  }
});

// show mute icon whenever a remote has muted their mic
agoraClient.on("mute-audio", function (evt) {
  window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_mute', true);
});

agoraClient.on("unmute-audio", function (evt) {
  window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_mute', false);
});

// show user icon whenever a remote has disabled their video
agoraClient.on("mute-video", function (evt) {
  var remoteId = evt.uid;
  // if the main user stops their video select a random user from the list
  if (remoteId != mainStreamId) {
    // if not the main vidiel then show the user icon
    window.AGORA_UTILS.toggleVisibility('#' + remoteId + '_no-video', true);
  }
});

agoraClient.on("unmute-video", function (evt) {
  window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_no-video', false);
});

// join a channel
function agoraJoinChannel(channelName) {
  var token = window.AGORA_TOKEN_UTILS.agoraGenerateToken();
  var userId = window.userID || 0; // set to null to auto generate uid on successfull connection
  agoraClient.join(token, channelName, userId, function(uid) {
    AgoraRTC.Logger.info("User " + uid + " join channel successfully");
    localStreams.camera.id = uid; // keep track of the stream uid 
    createCameraStream(uid);
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
    jQuery('#rejoin-container').hide();
    jQuery('#buttons-container').removeClass('hidden');

    var thisBtn = jQuery('#rejoin-btn');
    thisBtn.prop("disabled", false);
    thisBtn.find('.spinner-border').hide();

    AgoraRTC.Logger.info("getUserMedia successfully");
    // TODO: add check for other streams. play local stream full size if alone in channel
    localStream.play('local-video'); // play the given stream within the local-video div

    // publish local stream
    agoraClient.publish(localStream, function (err) {
      AgoraRTC.Logger.error("[ERROR] : publish local stream error: " + err);
    });
  
    window.AGORA_COMMUNICATION_UI.enableUiControls(localStream); // move after testing
    localStreams.camera.stream = localStream; // keep track of the camera stream for later
  }, function (err) {
    AgoraRTC.Logger.error("[ERROR] : getUserMedia failed", err);
  });
}


// REMOTE STREAMS UI
function addRemoteStreamMiniView(remoteStream){
  var streamId = remoteStream.getId();
  console.log('Adding remote to miniview:', streamId);
  // append the remote stream template to #remote-streams
  const remoteStreamsDiv = jQuery('#remote-streams');
  let playerFound = false;
  if (remoteStreamsDiv.length>0) {
    playerFound = true;
    remoteStreamsDiv.append(
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
  } else {
    const avatarCircleDiv = jQuery('#uid-'+streamId);
    if (avatarCircleDiv.length>0) {
      playerFound = true;
      const circle = avatarCircleDiv.find('.avatar-circle');
      circle.append(
        jQuery('<div/>', {'id': streamId + '_container',  'class': 'remote-stream-container'}).append(
          jQuery('<div/>', {'id': streamId + '_mute', 'class': 'mute-overlay'}).append(
            jQuery('<i/>', {'class': 'fas fa-microphone-slash'})
          ),
          jQuery('<div/>', {'id': streamId + '_no-video', 'class': 'no-video-overlay text-center'}).append(
            jQuery('<i/>', {'class': 'fas fa-user'})
          ),
          jQuery('<div/>', {'id': 'agora_remote_' + streamId, 'class': 'remote-video'})
        )
      )
      circle.find('img').hide();
    }
  }
  playerFound && remoteStream.play('agora_remote_' + streamId); 

  var containerId = '#' + streamId + '_container';
  jQuery(containerId).dblclick(function() {
    // play selected container as full screen - swap out current full screen stream
    remoteStreams[mainStreamId].stop(); // stop the main video stream playback
    addRemoteStreamMiniView(remoteStreams[mainStreamId]); // send the main video stream to a container
    const parentCircle = jQuery(containerId).parent();
    if (parentCircle.hasClass('avatar-circle')) {
      parentCircle.find('img').show();
    }
    jQuery(containerId).empty().remove(); // remove the stream's miniView container
    remoteStreams[streamId].stop() // stop the container's video stream playback
    remoteStreams[streamId].play('video-canvas'); // play the remote stream as the full screen video
    mainStreamId = streamId; // set the container stream id as the new main stream id
  });
}

function agoraLeaveChannel() {
  
  if(screenShareActive) {
    window.AGORA_SCREENSHARE_UTILS.stopScreenShare();
  }

  agoraClient.leave(function() {
    AgoraRTC.Logger.info("client leaves channel");
    localStreams.camera.stream.stop() // stop the camera stream playback
    agoraClient.unpublish(localStreams.camera.stream); // unpublish the camera stream
    localStreams.camera.stream.close(); // clean up and close the camera stream
    jQuery("#remote-streams").empty() // clean up the remote feeds
    //disable the UI elements
    jQuery("#mic-btn").prop("disabled", true);
    jQuery("#video-btn").prop("disabled", true);
    jQuery("#screen-share-btn").prop("disabled", true);
    jQuery("#exit-btn").prop("disabled", true);
    // hide the mute/no-video overlays
    window.AGORA_UTILS.toggleVisibility("#mute-overlay", false); 
    window.AGORA_UTILS.toggleVisibility("#no-local-video", false);

    jQuery('#rejoin-container').show();
    jQuery('#buttons-container').addClass('hidden');

    jQuery('#slick-avatars').slick('unslick').html('').slick(window.slickSettings);
    
    // show the modal overlay to join
    // jQuery("#modalForm").modal("show"); 
  }, function(err) {
    AgoraRTC.Logger.error("client leave failed ", err); //error handling
  });
}
