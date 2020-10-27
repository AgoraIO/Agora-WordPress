/*
 * JS Interface for Agora.io SDK
 */
// create client instances for camera (client) and screen share (screenClient)
var agoraClient = AgoraRTC.createClient({mode: 'rtc', codec: 'vp8'});

// stream references (keep track of active streams) 
window.remoteStreams = {}; // remote streams obj struct [id : stream] 

// keep track of streams
window.localStreams = {
  uid: '',
  camera: {
    camId: '',
    micId: '',
    stream: {}
  },
  screen: {
    id: "",
    stream: {}
  }
};

// keep track of devices
window.devices = {
  cameras: [],
  mics: []
}

var mainStreamId; // reference to main stream
var screenShareActive = false; // flag for screen share 

window.AGORA_COMMUNICATION_CLIENT = {
  initClientAndJoinChannel: initClientAndJoinChannel,
  agoraJoinChannel: agoraJoinChannel,
  agoraLeaveChannel: agoraLeaveChannel
};

function initClientAndJoinChannel(agoraAppId, channelName) {
  window.AGORA_RTM_UTILS.setupRTM(agoraAppId, channelName);

  // init Agora SDK
  agoraClient.init(agoraAppId, function () {
    AgoraRTC.Logger.info("AgoraRTC client initialized");
    agoraJoinChannel(channelName, function(err){
      if (err) {
        console.error(err);

        // TODO: Show Global error!
        window.AGORA_RTM_UTILS.leaveChannel();
      }
    }); // join channel upon successfull init
  }, function (err) {
    AgoraRTC.Logger.error("[ERROR] : AgoraRTC client init failed", err);
    window.AGORA_RTM_UTILS.leaveChannel();
  });
}


window.AGORA_UTILS.setupAgoraListeners();

// join a channel
function agoraJoinChannel(channelName, cb) {
  var token = window.AGORA_TOKEN_UTILS.agoraGenerateToken();
  var userId = window.userID || 0; // set to null to auto generate uid on successfull connection

  
  agoraClient.join(token, channelName, userId, async function(uid) {
    AgoraRTC.Logger.info("User " + uid + " join channel successfully");
    window.localStreams.camera.id = uid; // keep track of the stream uid 
    
    try {
      await window.AGORA_RTM_UTILS.joinChannel(uid);
      await createCameraStream(uid);
      cb && cb(null)
    } catch(err) {
      AgoraRTC.Logger.error("[ERROR] : join channel failed", err);
      cb && cb(err)
    }

  }, function(err) {
      AgoraRTC.Logger.error("[ERROR] : join channel failed", err);
      cb && cb(err)
  });
}

//camera validation 
async function isVideoAvailable() {
  let md = navigator.mediaDevices;
  if (!md || !md.enumerateDevices) return false;

  const devices = await md.enumerateDevices()
  return devices.some(device => 'videoinput' === device.kind);
}

// video streams for channel
function createCameraStream(uid, next) {
  async function runCameraStream(cb) {
    const hasVideo = await isVideoAvailable()
    const localStream = AgoraRTC.createStream({
      streamID: uid,
      audio: true,
      video: hasVideo,
      screen: false
    });
    localStream.setVideoProfile(window.cameraVideoProfile);
    localStream.on("accessAllowed", function() {
      if(window.devices.cameras.length === 0 && window.devices.mics.length === 0) {
        AgoraRTC.Logger.info('[DEBUG] : checking for cameras & mics');
        window.AGORA_UTILS.getCameraDevices();
        window.AGORA_UTILS.getMicDevices();
      }
      AgoraRTC.Logger.info("accessAllowed");
      if(!hasVideo){
        const msg = {
          text: "USER_JOINED_WITHOUT_VIDEO**"+uid,
          messageType: "TEXT"
        }
        window.AGORA_RTM_UTILS.sendChannelMessage(msg)
      }
    });

    localStream.on("accessDenied", function() {
      // alert('denied!')
    })

    localStream.init(function initSuccess() {
      jQuery('#rejoin-container').hide();
      jQuery('#buttons-container').removeClass('hidden');

      var thisBtn = jQuery('#rejoin-btn');
      thisBtn.prop("disabled", false);
      thisBtn.find('.spinner-border').hide();

      AgoraRTC.Logger.info("getUserMedia successfully");
      try {
        localStream.play('local-video'); // play the given stream within the local-video div
        // publish local stream
        agoraClient.publish(localStream, function (err) {
          AgoraRTC.Logger.error("[ERROR] : publish local stream error: " + err);
        });
      
        window.AGORA_COMMUNICATION_UI.enableUiControls(localStream); // move after testing
        window.localStreams.camera.stream = localStream; // keep track of the camera stream for later

        cb && cb(null)
      } catch(ex) {
        // TODO: Show this error somewhere
        AgoraRTC.Logger.error('Stream error...', ex);
        agoraLeaveChannel();
        alert("Your video cannot be started!")
        cb && cb(ex)
      }
    }, function initError(err) {
      AgoraRTC.Logger.error("[ERROR] : getUserMedia failed", err);

      if (err.msg==='NotAllowedError') {

        const msg = {
          text: "USER_JOINED_WITHOUT_PERMISSIONS**"+uid,
          messageType:"TEXT"
        }
        window.AGORA_RTM_UTILS.sendChannelMessage(msg)
        
        window.AGORA_COMMUNICATION_UI.enableExit()
        window.AGORA_UTILS.showPermissionsModal()
      } else {
        cb && cb(err)
      }

    });
  }

  if (next) {
    runCameraStream(next);
  } else {
    return new Promise((resolve, reject) => {
      runCameraStream(err => {
        if (err) { reject(err); }
        else { resolve() }
      })
    })
  }
}

function agoraLeaveChannel() {
  
  if(screenShareActive) {
    window.AGORA_SCREENSHARE_UTILS.stopScreenShare();
  }

  window.dispatchEvent(new CustomEvent("agora.leavingChannel"));

  agoraClient.leave(function() {
    AgoraRTC.Logger.info("client leaves channel");
    const camStream = window.localStreams.camera.stream;
    if (camStream && camStream.stop) {
      camStream.stop() // stop the camera stream playback
      agoraClient.unpublish(camStream); // unpublish the camera stream
      camStream.close(); // clean up and close the camera stream
    }
    jQuery(".remote-stream-container").empty() // clean up the remote feeds
    //disable the UI elements
    jQuery("#mic-btn").prop("disabled", true);
    jQuery("#video-btn").prop("disabled", true);
    jQuery("#screen-share-btn").prop("disabled", true);
    jQuery("#exit-btn").prop("disabled", true);
    jQuery("#cloud-recording-btn").prop("disabled", true);
    
    // hide the mute/no-video overlays
    window.AGORA_UTILS.toggleVisibility("#mute-overlay", false); 
    window.AGORA_UTILS.toggleVisibility("#no-local-video", false);

    jQuery('#rejoin-container').show();
    jQuery('#buttons-container').addClass('hidden');

    // leave also RTM Channel
    window.AGORA_RTM_UTILS.leaveChannel();

    window.dispatchEvent(new CustomEvent("agora.leavedChannel"));
    
    // show the modal overlay to join
    // jQuery("#modalForm").modal("show"); 
  }, function(err) {
    AgoraRTC.Logger.error("client leave failed ", err); //error handling
  });
}
