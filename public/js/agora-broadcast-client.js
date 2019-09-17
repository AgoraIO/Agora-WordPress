/**
 * Agora Broadcast Client 
 */

// join a channel
function joinChannel() {
  var token = generateToken();
  var userID = 0; // set to null to auto generate uid on successfull connection

  // set the role
  window.agoraClient.setClientRole('host', function() {
    console.log('Client role set as host.');
  }, function(e) {
    console.log('setClientRole failed', e);
  });
  
  // window.agoraClient.join(token, 'allThingsRTCLiveStream', 0, function(uid) {
  window.agoraClient.join(token, window.channelName, userID, function(uid) {
      createCameraStream(uid, {});
      window.localStreams.uid = uid; // keep track of the stream uid  
      console.log('User ' + uid + ' joined channel successfully');
  }, function(err) {
      console.log('[ERROR] : join channel failed', err);
  });
}

// video streams for channel
function createCameraStream(uid, deviceIds) {
  console.log('Creating stream with sources: ' + JSON.stringify(deviceIds));

  var localStream = AgoraRTC.createStream({
    streamID: uid,
    audio: true,
    video: true,
    screen: false
  });
  localStream.setVideoProfile(cameraVideoProfile);

  // The user has granted access to the camera and mic.
  localStream.on("accessAllowed", function() {
    if(devices.cameras.length === 0 && devices.mics.length === 0) {
      console.log('[DEBUG] : checking for cameras & mics');
      getCameraDevices();
      getMicDevices();
    }
    console.log("accessAllowed");
  });
  // The user has denied access to the camera and mic.
  localStream.on("accessDenied", function() {
    console.log("accessDenied");
  });

  localStream.init(function() {
    console.log('getUserMedia successfully');
    localStream.play('full-screen-video'); // play the local stream on the main div
    // publish local stream

    if($.isEmptyObject(window.localStreams.camera.stream)) {
      enableUiControls(localStream); // move after testing
    } else {
      //reset controls
      $("#mic-btn").prop("disabled", false);
      $("#video-btn").prop("disabled", false);
      $("#exit-btn").prop("disabled", false);
    }

    window.agoraClient.publish(localStream, function (err) {
      console.log('[ERROR] : publish local stream error: ' + err);
    });

    window.localStreams.camera.stream = localStream; // keep track of the camera stream for later
  }, function (err) {
    console.log('[ERROR] : getUserMedia failed', err);
  });
}

function leaveChannel() {

  window.agoraClient.leave(function() {
    console.log('client leaves channel');
    window.localStreams.camera.stream.stop() // stop the camera stream playback
    window.localStreams.camera.stream.close(); // clean up and close the camera stream
    window.agoraClient.unpublish(window.localStreams.camera.stream); // unpublish the camera stream
    //disable the UI elements
    $('#mic-btn').prop('disabled', true);
    $('#video-btn').prop('disabled', true);
    $('#exit-btn').prop('disabled', true);
    $("#add-rtmp-btn").prop("disabled", true);
    $("#rtmp-config-btn").prop("disabled", true);
  }, function(err) {
    console.log('client leave failed ', err); //error handling
  });
}

// use tokens for added security
function generateToken() {
  return null; // TODO: add a token generation
}

function changeStreamSource (deviceIndex, deviceType) {
  console.log('Switching stream sources for: ' + deviceType);
  var deviceId;
  var existingStream = false;
  
  if (deviceType === "video") {
    deviceId = devices.cameras[deviceIndex].deviceId
  }

  if(deviceType === "audio") {
    deviceId = devices.mics[deviceIndex].deviceId;
  }

  window.localStreams.camera.stream.switchDevice(deviceType, deviceId, function(){
    console.log('successfully switched to new device with id: ' + JSON.stringify(deviceId));
    // set the active device ids
    if(deviceType === "audio") {
      window.localStreams.camera.micId = deviceId;
    } else if (deviceType === "video") {
      window.localStreams.camera.camId = deviceId;
    } else {
      console.log("unable to determine deviceType: " + deviceType);
    }
  }, function(){
    console.log('failed to switch to new device with id: ' + JSON.stringify(deviceId));
  });
}

// helper methods
function getCameraDevices() {
  console.log("Checking for Camera Devices.....")
  window.agoraClient.getCameras (function(cameras) {
    devices.cameras = cameras; // store cameras array
    cameras.forEach(function(camera, i){
      var name = camera.label.split('(')[0];
      var optionId = 'camera_' + i;
      var deviceId = camera.deviceId;
      if(i === 0 && window.localStreams.camera.camId === ''){
        window.localStreams.camera.camId = deviceId;
      }
      $('#camera-list').append('<a class="dropdown-item" id="' + optionId + '">' + name + '</a>');
    });
    $('#camera-list a').click(function(event) {
      var index = event.target.id.split('_')[1];
      changeStreamSource (index, "video");
    });
  });
}

function getMicDevices() {
  console.log("Checking for Mic Devices.....")
  window.agoraClient.getRecordingDevices(function(mics) {
    devices.mics = mics; // store mics array
    mics.forEach(function(mic, i){
      var name = mic.label.split('(')[0];
      var optionId = 'mic_' + i;
      var deviceId = mic.deviceId;
      if(i === 0 && window.localStreams.camera.micId === ''){
        window.localStreams.camera.micId = deviceId;
      }
      if(name.split('Default - ')[1] != undefined) {
        name = '[Default Device]' // rename the default mic - only appears on Chrome & Opera
      }
      $('#mic-list').append('<a class="dropdown-item" id="' + optionId + '">' + name + '</a>');
    }); 
    $('#mic-list a').click(function(event) {
      var index = event.target.id.split('_')[1];
      changeStreamSource (index, "audio");
    });
  });
}

function startLiveTranscoding() {
  console.log("start live transcoding"); 
  var rtmpUrl = $('#rtmp-url').val();
  var width = parseInt($('#window-scale-width').val(), 10);
  var height = parseInt($('#window-scale-height').val(), 10);

  var configRtmp = {
    width: width,
    height: height,
    videoBitrate: parseInt($('#video-bitrate').val(), 10),
    videoFramerate: parseInt($('#framerate').val(), 10),
    lowLatency: ($('#low-latancy').val() === 'true'),
    audioSampleRate: parseInt($('#audio-sample-rate').val(), 10),
    audioBitrate: parseInt($('#audio-bitrate').val(), 10),
    audioChannels: parseInt($('#audio-channels').val(), 10),
    videoGop: parseInt($('#video-gop').val(), 10),
    videoCodecProfile: parseInt($('#video-codec-profile').val(), 10),
    userCount: 1,
    userConfigExtraInfo: {},
    backgroundColor: parseInt($('#background-color-picker').val(), 16),
    transcodingUsers: [{
      uid: window.localStreams.uid,
      alpha: 1,
      width: width,
      height: height,
      x: 0,
      y: 0,
      zOrder: 0
    }],
  };

  // set live transcoding config
  window.agoraClient.setLiveTranscoding(configRtmp);
  if(rtmpUrl !== '') {
    window.agoraClient.startLiveStreaming(rtmpUrl, true)
    externalBroadcastUrl = rtmpUrl;
    addExternalTransmitionMiniView(rtmpUrl)
  }
}

function addExternalSource() {
  var externalUrl = $('#external-url').val();
  var width = parseInt($('#external-window-scale-width').val(), 10);
  var height = parseInt($('#external-window-scale-height').val(), 10);

  var injectStreamConfig = {
    width: width,
    height: height,
    videoBitrate: parseInt($('#external-video-bitrate').val(), 10),
    videoFramerate: parseInt($('#external-framerate').val(), 10),
    audioSampleRate: parseInt($('#external-audio-sample-rate').val(), 10),
    audioBitrate: parseInt($('#external-audio-bitrate').val(), 10),
    audioChannels: parseInt($('#external-audio-channels').val(), 10),
    videoGop: parseInt($('#external-video-gop').val(), 10)
  };

  // set live transcoding config
  window.agoraClient.addInjectStreamUrl(externalUrl, injectStreamConfig)
  injectedStreamURL = externalUrl;
  // TODO: ADD view for external url (similar to rtmp url)
}

// RTMP Connection (UI Component)
function addExternalTransmitionMiniView(rtmpUrl) {
  var container = $('#rtmp-controlers');
  // append the remote stream template to #remote-streams
  container.append(
    $('<div/>', {'id': 'rtmp-container',  'class': 'container row justify-content-end mb-2'}).append(
      $('<div/>', {'class': 'pulse-container'}).append(
          $('<button/>', {'id': 'rtmp-toggle', 'class': 'btn btn-lg col-flex pulse-button pulse-anim mt-2'})
      ),
      $('<input/>', {'id': 'rtmp-url', 'val': rtmpUrl, 'class': 'form-control col-flex" value="rtmps://live.facebook.com', 'type': 'text', 'disabled': true}),
      $('<button/>', {'id': 'removeRtmpUrl', 'class': 'btn btn-lg col-flex close-btn'}).append(
        $('<i/>', {'class': 'fas fa-xs fa-trash'})
      )
    )
  );
  
  $('#rtmp-toggle').click(function() {
    if ($(this).hasClass('pulse-anim')) {
      window.agoraClient.stopLiveStreaming(externalBroadcastUrl)
    } else {
      window.agoraClient.startLiveStreaming(externalBroadcastUrl, true)
    }
    $(this).toggleClass('pulse-anim');
    $(this).blur();
  });

  $('#removeRtmpUrl').click(function() { 
    window.agoraClient.stopLiveStreaming(externalBroadcastUrl);
    externalBroadcastUrl = '';
    $('#rtmp-container').remove();
  });

}
