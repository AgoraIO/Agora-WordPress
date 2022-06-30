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
    stream: {},
    userDetails: {}
  },
  tmpCameraStream: {},
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
};

function initClientAndJoinChannel(agoraAppId, channelName) {
  //window.AGORA_RTM_UTILS.setupRTM(agoraAppId, channelName);

  // init Agora SDK
  agoraClient.init(agoraAppId, function () {
    AgoraRTC.Logger.info("AgoraRTC client initialized");
    window.AGORA_UTILS.agoraJoinChannel(channelName, function(err){
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