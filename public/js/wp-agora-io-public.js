// https://www.paulirish.com/2009/throttled-smartresize-jquery-event-handler/
(function($,sr){

  // debouncing function from John Hann
  // http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/
  var debounce = function (func, threshold, execAsap) {
      var timeout;

      return function debounced () {
          var obj = this, args = arguments;
          function delayed () {
              if (!execAsap)
                  func.apply(obj, args);
              timeout = null;
          };

          if (timeout)
              clearTimeout(timeout);
          else if (execAsap)
              func.apply(obj, args);

          timeout = setTimeout(delayed, threshold || 150);
      };
  }
  // smartresize 
  jQuery.fn[sr] = function(fn){  return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr); };

})(jQuery,'smartresize');



function changeStreamSource (deviceIndex, deviceType, stream='') {
  AgoraRTC.Logger.info('Switching stream sources for: ' + deviceType);
  var deviceId;
  var existingStream = false;
  
  if (deviceType === "video") {
    deviceId = window.devices.cameras[deviceIndex].deviceId
  }

  if(deviceType === "audio") {
    deviceId = window.devices.mics[deviceIndex].deviceId;
  }

  let switchDeviceStream = window.localStreams.camera.stream;

  if(stream == 'tmpStream'){
    switchDeviceStream = window.localStreams.tmpCameraStream;
  }

  switchDeviceStream.switchDevice(deviceType, deviceId, function(){
    AgoraRTC.Logger.info('successfully switched to new device with id: ' + JSON.stringify(deviceId));
    // set the active device ids
    if(deviceType === "audio") {
      sessionStorage.setItem("microphoneId", deviceId)
      window.localStreams.camera.micId = deviceId;
    } else if (deviceType === "video") {
      sessionStorage.setItem("cameraId", deviceId)
      window.localStreams.camera.camId = deviceId;
    } else {
      AgoraRTC.Logger.warning("unable to determine deviceType: " + deviceType);
    }
  }, function(err){
    AgoraRTC.Logger.error('failed to switch to new device with id: ' + JSON.stringify(deviceId));
    console.error(err);
  });
}

// helper methods
function getCameraDevices() {
  AgoraRTC.Logger.info("Checking for Camera window.devices.....")
  window.agoraClient.getCameras (function(cameras) {
    window.devices.cameras = cameras; // store cameras array

    let camOpts = '';

    cameras.forEach(function(camera, i){
      var name = camera.label.split('(')[0];
      var optionId = 'camera_' + i;
      var deviceId = camera.deviceId;
      if(i === 0 && window.localStreams.camera.camId === ''){
        window.localStreams.camera.camId = deviceId;
      }
      camOpts+='<option value="'+optionId+'">' + name + '</option>';
      jQuery('#camera-list').append('<a class="dropdown-item" id="' + optionId + '">' + name + '</a>');
    });

    jQuery('#test-device-camera-list select#test-device-camera-options').append(camOpts);


    jQuery('#camera-list a').click(function(event) {
      changeCameraDevice(event.target.id)
    });

    jQuery('#test-device-camera-list select').on('change', function() {
      changeCameraDevice(jQuery(this).val(), 'tmpStream')
    });
    

  });
}

function changeCameraDevice(target_id, stream=''){
  var index = target_id.split('_')[1];
  changeStreamSource (index, "video", stream);
}

function getMicDevices() {
  AgoraRTC.Logger.info("Checking for Mic window.devices.....")
  window.agoraClient.getRecordingDevices(function(mics) {
    window.devices.mics = mics; // store mics array

    let micOpts = '';

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
      micOpts+='<option value="'+optionId+'">' + name + '</option>';
      jQuery('#mic-list').append('<a class="dropdown-item" id="' + optionId + '">' + name + '</a>');
    });
        
    jQuery('#test-device-mic-list select#test-device-mic-options').append(micOpts);
    
    jQuery('#mic-list a').click(function(event) {
      changeMicDevice(event.target.id);
    });

    jQuery('body select#test-device-mic-options').on('change', function() {
      changeMicDevice(jQuery(this).val(), "tmpStream")
    });

  });
}

function changeMicDevice(target_id, stream=''){
  var index = target_id.split('_')[1];
  changeStreamSource (index, "audio", stream);
}


window.unselectedVideoControlsButtonsColor = '';
window.selectedVideoControlsButtonsColor = '';
window.otherButtonsColor = '';
window.panelsBackgroundColor = '';
window.videoMutedBackgroundColor = '';

window.AGORA_UTILS = {

  getMicDevices: getMicDevices,
  getCameraDevices: getCameraDevices,

  toggleFullscreen: function() {
    const root = jQuery('#agora-root');
    if(document.webkitFullscreenElement) {
      document.webkitCancelFullScreen();
      if (root.hasClass('agora-fullscreen')) {
        root.removeClass('agora-fullscreen')
      }
    } else {
      root[0].webkitRequestFullScreen();
      if (!root.hasClass('agora-fullscreen')) {
        root.addClass('agora-fullscreen')
      }
    };

    const remoteEl = document.querySelector('.screenshare-container')
    if (remoteEl) {
      const divWidth = remoteEl.getBoundingClientRect().width;
      remoteEl.style.height = (divWidth / 1.35) + 'px'; // ratio 16:10
    }
  },

  showErrorMessage: function(error) {
    if (error) {
      const ERROR_SHOW_TIME = 10000; // 10 seconds
      let msg = '';
      console.error(error);
      if (error.responseJSON) {
        msg = Object.values(error.responseJSON.errors).join(', ')
      } else {
        msg = typeof error === 'string' ? error : error.toString();
      }

      console.error('Error:', msg)
      const errorEl = jQuery('#error-msg');
      errorEl.parent().show();
      errorEl.html('Agora Error: ' + msg);
      errorEl.css('opacity', 1)
      setTimeout(function(el) {
        el.css('opacity', 0)
        setTimeout(function(){
          el.parent().hide();
        }, 500);
      }, ERROR_SHOW_TIME, errorEl)
    }
  },

  agoraApiRequest: function (endpoint_url, endpoint_data) {
    var ajaxRequestParams = {
      method: 'POST',
      url: endpoint_url,
      data: endpoint_data
    };
    return jQuery.ajax(ajaxRequestParams)
  },

  toggleBtn: function (btn){

    if(window.unselectedVideoControlsButtonsColor!="" && jQuery(btn).hasClass('btn-dark') || jQuery(btn).hasClass('btn-danger')){
      jQuery(btn).css('background-color', window.unselectedVideoControlsButtonsColor);
    } else if(window.selectedVideoControlsButtonsColor!="") {
      jQuery(btn).css('background-color', window.selectedVideoControlsButtonsColor);
    }

    btn.toggleClass('btn-dark').toggleClass('btn-danger');
  },

  toggleVisibility: function (elementID, visible) {
    const el = document.getElementById(elementID.replace('#', ''));
    if (el) {
      el.style.display = visible ? "flex" : "none";
    }
  },

  agora_getUserAvatar: function (user_id, cb) {
    var params = {
      action: 'get_user_avatar', // wp ajax action
      uid: user_id, // needed to get the avatar from the WP user
    };
    window.AGORA_UTILS.agoraApiRequest(ajax_url, params).done(function(data) {
      if (cb) {
        cb(data);
      }
    }).fail(function(err) {
      console.error('Avatar not available:', err);
    });
  },

  updateUsersCounter: function(count) {
    // console.log('updating to', count);
    jQuery('#count-users').html(count);

    let countClass = count;
    switch(count) {
      case 3:
        countClass = '3';
        break;
      case 4:
        countClass = '4';
        break;
      case 5:
      case 6:
        countClass = '5-6';
        break;
      case 7:
      case 8:
      case 9:
        countClass = '7-9';
        break;
    }

    // default for 10 or more users:
    if (count>9) {
      countClass = '10-12'
    }

    // Update users class to keep layout organized
    document.getElementById('screen-users').classList = "screen-users screen-users-" + countClass;
  },

  handleSpeakerViewStreamsOnRemove: function(streamId){
    if(window.isSpeakerViewWithRemoteRight){
      let remoteContainerID = '#' + streamId + '_container';
      console.log("hlwremoteContainerID", remoteContainerID)
      let removedStreamParentClass = jQuery(remoteContainerID).parent().attr('class');

      if(removedStreamParentClass == 'main-screen-stream-section'){

        /* If removed div is from main screen (big screen), play local video in main screen */
        local_stream_div_id = window.agoraMode==='communication' ? 'local-video' : 'full-screen-video';
        window.localStreams.camera.stream.stop();
        let localVideoContainer = jQuery('.speaker-view #screen-users').find('#'+local_stream_div_id).prop("outerHTML");
        jQuery('.speaker-view .main-screen #main-screen-stream-section').html(localVideoContainer);
        window.localStreams.camera.stream.play(local_stream_div_id);

        /* Remove local stream div from right side stream after setting it in main stream */
        jQuery('.speaker-view #screen-users').find("#"+local_stream_div_id).parent().empty().remove();
      } else {
        
      } 
    }
  },

  deleteRemoteStream: function(streamId) {
    window.remoteStreams[streamId].stream.stop(); // stop playing the feed
    delete window.remoteStreams[streamId]; // remove stream from list
    let remoteContainerID = '#' + streamId + '_container';

    if(window.isSpeakerViewWithRemoteRight){
      remoteContainerID = jQuery(remoteContainerID).parent().closest('.remote-stream-main-container');
      window.AGORA_UTILS.handleSpeakerViewStreamsOnRemove(streamId);
      jQuery(remoteContainerID).empty().remove();
      if ( jQuery('#screen-users').children().length == 0 ) {
        jQuery(".speaker-view .main-screen").css('width', '100%');
        jQuery(".speaker-view .screen-users").css('width', '0%');
      }
    } else {
      jQuery(remoteContainerID).empty().remove();
    }
    
    handleGhostMode(streamId, 'remote');
    
  },

  setupAgoraListeners: function() {

    /* Handle Active Speaker */
    const THRESHOLD_AUDIO_LEVEL = 1;
    window.agoraClient.enableAudioVolumeIndicator();

    window.agoraClient.on("volume-indicator", function(evt){
      jQuery('.activeSpeaker').removeClass('activeSpeaker');
      evt.attr.forEach(function(volume, index){
        console.log(`${index} UID ${volume.uid} Level ${volume.level}`);
        if(volume.level>THRESHOLD_AUDIO_LEVEL){
          jQuery('body #' + volume.uid + '_container').addClass('activeSpeaker');
        }
      });
    });
    /* End Handle Active Speaker */

    // show mute icon whenever a remote has muted their mic
    window.agoraClient.on("mute-audio", function muteAudio(evt) {
      window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_mute', true);
      console.log("callMuteAudioGhostCheck")
      handleGhostMode(evt.uid, 'remote');
      handleRemoteStreamControlsIcons(evt.uid);
    });

    window.agoraClient.on("unmute-audio", function unmuteAudio(evt) {
      window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_mute', false);
      console.log("callUnMuteAudioGhostCheck")
      handleGhostMode(evt.uid, 'remote');
      handleRemoteStreamControlsIcons(evt.uid);
    });

    // show user icon whenever a remote has disabled their video
    window.agoraClient.on("mute-video", async function muteVideo(evt) {
      const remoteId = evt.uid;

      window.AGORA_UTILS.toggleVisibility('#' + remoteId + '_no-video', true);

      console.log("remoteVideoMuted")
      console.log("callMuteVideoGhostCheck")

      // if the main user stops their video select a random user from the list
      handleGhostMode(evt.uid, 'remote');
      handleMutedVideoBackgroundColor(evt.uid, 'remote');
      let userAvatar = '';
      if(window.remoteStreams[remoteId]){
        console.log("hlwremoteStreams", window.remoteStreams[remoteId])
        console.log("hlwuserAvtar", window.remoteStreams[remoteId].userDetails)
        if(typeof window.remoteStreams[remoteId].userDetails!='undefined'){
          userAvatar = window.remoteStreams[remoteId].userDetails.avtar;
        }
      }
      if(userAvatar!=''){
        jQuery('body #'+ remoteId + '_no-video').html('<img src="'+userAvatar.url+'" width="'+userAvatar.width+'" height="'+userAvatar.height+'" />')
      }
      handleRemoteStreamControlsIcons(evt.uid);
    });

    agoraClient.on("unmute-video", function unmuteVideo(evt) {
      window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_no-video', false);
      console.log("callUnMuteVideoGhostCheck")
      handleGhostMode(evt.uid, 'remote');
      handleRemoteStreamControlsIcons(evt.uid);
    });

    // remove the remote-container when a user leaves the channel
    window.agoraClient.on("peer-leave", async function peerLeave(evt) {
      if (!evt || !evt.stream) {
        console.error('Stream undefined cannot be removed', evt);
        return false;
      }
      console.log('peer-leave:', evt);
      var streamId = evt.stream.getId(); // the the stream id
      jQuery('#uid-'+streamId).remove();

      if(window.remoteStreams[streamId] !== undefined) {
        window.AGORA_UTILS.deleteRemoteStream(streamId);
        // always is +1 due to the remote streams + local user
        const usersCount = Object.keys(window.remoteStreams).length + 1
        window.AGORA_UTILS.updateUsersCounter(usersCount)
      }

      const remoteStream = evt.stream;
      const isInjectedStream = window.injectedStreamURL && window.injectedStreamURL!=="";
     
      if (window.screenshareClients[streamId] || isInjectedStream) {
        typeof remoteStream.stop==='function' && remoteStream.stop();
        const remoteContainerID = '#' + streamId + '_container';
        jQuery(remoteContainerID).empty().remove();
        const streamsContainer = jQuery('#screen-zone');
        streamsContainer.toggleClass('sharescreen');

        if (isInjectedStream) {
          window.injectedStreamURL = false;
          window.AGORA_BROADCAST_UI.toggleCaptureStreamBtn(null, 'stopped');
        } else {
          delete window.screenshareClients[streamId];
        }
        handleGhostMode(streamId, 'remote');
      }

      if (window.AGORA_CLOUD_RECORDING.isCloudRecording) {
        window.AGORA_CLOUD_RECORDING.updateLayout();
      }
    });


    // connect remote streams
    window.agoraClient.on('stream-added', function streamAdded(evt) {
      const stream = evt.stream;
      const streamId = stream.getId();
      console.log('AgoraRTC.Logger.info("new stream added: " + streamId);', streamId)
      AgoraRTC.Logger.info("new stream added: " + streamId);

      // Check if the stream is the local screen
      if (streamId != window.localStreams.screen.id) {

        let remoteStream = stream;
        let remoteId = streamId;
        window.remoteStreams[remoteId] = { stream: remoteStream };

        /* Set the remote stream details alongwith user avtar */
        window.AGORA_UTILS.agora_getUserAvatar(remoteId, function getUserAvatar(avatarData) {
          let userAvatar = '';
          if (avatarData && avatarData.user && avatarData.avatar) {
            userAvatar = avatarData.avatar
          }
          window.remoteStreams[remoteId].userDetails = {avtar: userAvatar};
        });

        const isInjectedStream = window.injectedStreamURL && window.injectedStreamURL!=="";
        if (window.screenshareClients[remoteId] || isInjectedStream) {
        
        } else {
          // show new stream on screen:
          window.AGORA_UTILS.addRemoteStreamView(remoteStream);
        }

        // // Subscribe to the stream.
        agoraClient.subscribe(stream, function (err) {
          AgoraRTC.Logger.error("[ERROR] : subscribe stream failed", err);
          window.AGORA_UTILS.deleteRemoteStream(stream.getId());
          var streamId = evt.stream.getId(); // the the stream id
          jQuery('#uid-'+streamId).remove();

          if(window.remoteStreams[streamId] !== undefined) {
            window.AGORA_UTILS.deleteRemoteStream(streamId);
          }
        });

      } else {
        // show this stream on main screen
      }
    });

    window.agoraClient.on('stream-subscribed', function streamSubscribed(evt) {
      var remoteStream = evt.stream;
      var remoteId = remoteStream.getId();
      //window.remoteStreams[remoteId] = { stream: remoteStream };
      // console.log('Stream subscribed:', remoteId);

      console.log("Subscribe remote stream successfully:")
      AgoraRTC.Logger.info("Subscribe remote stream successfully: " + window.screenshareClients);

      const isInjectedStream = window.injectedStreamURL && window.injectedStreamURL!=="";
      if (window.screenshareClients[remoteId] || isInjectedStream) {
        // this is a screen share stream:
        console.log('Screen stream arrived:');
        window.AGORA_SCREENSHARE_UTILS.addRemoteScreenshare(remoteStream);

        if (isInjectedStream) {
          window.AGORA_BROADCAST_UI.toggleCaptureStreamBtn(null, 'started');
        }
      } else {
        // always add 1 due to the remote streams + local user
        const usersCount = Object.keys(window.remoteStreams).length + 1
        window.AGORA_UTILS.updateUsersCounter(usersCount);
      }

      if (window.AGORA_CLOUD_RECORDING.isCloudRecording) {
        window.AGORA_CLOUD_RECORDING.updateLayout();
      }

      // let remoteStream = stream;
      //   let remoteId = streamId;
      //   window.remoteStreams[remoteId] = { stream: remoteStream };

      //   /* Set the remote stream details alongwith user avtar */
      //   window.AGORA_UTILS.agora_getUserAvatar(remoteId, function getUserAvatar(avatarData) {
      //     let userAvatar = '';
      //     if (avatarData && avatarData.user && avatarData.avatar) {
      //       userAvatar = avatarData.avatar
      //     }
      //     window.remoteStreams[remoteId].userDetails = {avtar: userAvatar};
      //   });

      //   const isInjectedStream = window.injectedStreamURL && window.injectedStreamURL!=="";
      //   if (window.screenshareClients[remoteId] || isInjectedStream) {
        
      //   } else {
      //     // show new stream on screen:
      //     window.AGORA_UTILS.addRemoteStreamView(remoteStream);
      //   }

    });

    // Listener for Agora RTM Events
    window.addEventListener('agora.rtmMessageFromChannel', receiveRTMMessage);

  },

  // REMOTE STREAMS UI
  addRemoteStreamView: function(remoteStream, cond='') {

    const streamId = remoteStream.getId();
    console.log('Adding remote to main view:', streamId);
    // append the remote stream template to #remote-streams

    /* Incase of speaker view */
    if(jQuery('#screen-users').length==0){
      jQuery('body #screen-zone').append('<div id="screen-users"></div>');
    }
    jQuery('.speaker-view .main-screen').css('width', '80%');
    jQuery(".speaker-view .screen-users").css('width', '20%');

    const streamsContainer = jQuery('#screen-users');

    // avoid duplicate users in case there are errors removing old users and rejoining
    const old = streamsContainer.find(`#${streamId}_container`)
    if (old && old[0]) { old[0].remove() }
    if(window.isSpeakerViewWithRemoteRight){
      streamsContainer.append(
        jQuery('<div/>', {'class': 'remote-stream-main-container'}).append(
          jQuery('<div/>', {'id': streamId + '_container',  'class': 'user remote-stream-container', 'rel': streamId}).append(
            jQuery('<div/>', {'id': streamId + '_mute', 'class': 'mute-overlay'}).append(
                jQuery('<i/>', {'class': 'fas fa-microphone-slash'})
            ),
            jQuery('<div/>', {'id': streamId + '_no-video', 'class': 'no-video-overlay text-center'}).append(
              jQuery('<i/>', {'class': 'fas fa-user'})
            ),
            jQuery('<div/>', {'id': 'agora_remote_' + streamId, 'class': 'remote-video'})
          )
        )
      );
    } else {
      streamsContainer.append(
        jQuery('<div/>', {'id': streamId + '_container',  'class': 'user remote-stream-container', 'rel': streamId}).append(
          jQuery('<div/>', {'id': streamId + '_mute', 'class': 'mute-overlay'}).append(
              jQuery('<i/>', {'class': 'fas fa-microphone-slash'})
          ),
          jQuery('<div/>', {'id': streamId + '_no-video', 'class': 'no-video-overlay text-center'}).append(
            jQuery('<i/>', {'class': 'fas fa-user'})
          ),
          jQuery('<div/>', {'id': 'agora_remote_' + streamId, 'class': 'remote-video'})
        )
      );
    }

    remoteStream.play('agora_remote_' + streamId, function(err){

      if ((err && err.status !== "aborted") || (err && err.audio && err.audio.status !== "aborted")){
        console.log("hnjiErrorDuringPlay")
        jQuery('body #' + streamId + '_container').prepend(
          addAudioErrorGesture(streamId)
        )
      }
      // console.log("streamPlayGhostCheck", streamId)
      // console.log("streamPlayGhostCheck", remoteStream.getAudioTrack().enabled)
      handleGhostMode(streamId, 'remote');
    });
  },


  showPermissionsModal: function() {
    const browser = window.AGORA_UTILS.getBrowser();
    const img = document.getElementById('img-permissions-instructions');
    
    let textId = 'text-permissions-URL';
    if (browser==='unknow') {
      textId = 'text-permissions-any';
      img.classList.add('hidden');
    }
    document.getElementById(textId).classList.remove('hidden')

    imgSrc = window.agora_base_url.replace('public/', 'imgs/permissions/') + browser + '.jpg'
    img.setAttribute('src', imgSrc)

    jQuery('#permissions-notification-modal').modal('toggle')
  },

  getBrowser: function() {
      // Get the user-agent string 
      let userAgentString = navigator.userAgent;

      // Detect Chrome 
      if (userAgentString.indexOf("Chrome") > -1) {
        return "chrome"
      }
    
      // Detect Internet Explorer 
      // if (userAgentString.indexOf("MSIE") > -1 || userAgentString.indexOf("rv:") > -1) {
      //   return "ie"
      // }
    
      // Detect Firefox 
      if (userAgentString.indexOf("Firefox") > -1) {
        return "firefox"
      }
    
      // Detect Safari   
      if (userAgentString.indexOf("Safari") > -1) {
        return "safari"
      }
      
      // Detect Opera 
      // if (userAgentString.indexOf("OP") > -1) {
      //   return "opera"
      // }

      return "unknow"
  },

  agora_generateAjaxTokenRTM: function (cb, uid) {
    window.AGORA_UTILS.agora_generateAjaxToken(cb, uid, 'RTM')
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

  //Join and publish Local Stream
  async joinVideoCall (localStream, cond=''){

    sessionStorage.setItem("deviceTested", "Yes");

    jQuery("body #local-video").css('width', '100%');
    jQuery("body #full-screen-video").css('width', '100%');

    window.localStreams.tmpCameraStream.stop();

    jQuery('body div#test-device-section').remove();

    jQuery('body .agora-footer').css('display', 'flex');

    window.pre_call_device_test_enabled = 0;
    sessionStorage.setItem("deviceTested", "Yes");
    if(window.channel_type == 'communication'){
      await window.AGORA_COMMUNICATION_CLIENT.agoraJoinChannel(window.channelName);
    } else {
      await window.AGORA_BROADCAST_CLIENT.agoraJoinChannel();
    }
  }
}


window.AGORA_CLOUD_RECORDING = {
  isCloudRecording: false,

  startVideoRecording: function (cb) {

    let user_id = (window.userID == 0) ? window.localStreams.uid : window.userID;

    var params = {
      action: 'cloud_record', // wp ajax action
      sdk_action: 'start-recording',
      cid: window.channelId,
      cname: window.channelName,
      uid: user_id,
      token: window.agoraToken
    };
    window.AGORA_UTILS.agoraApiRequest(ajax_url, params).done(function(res) {
      // var startRecordURL = agoraAPI + window.agoraAppId + '/cloud_recording/resourceid/' + res.resourceId + '/mode/mix/start';
      // console.log(res);
      if (res && res.sid) {
        window.resourceId = res.resourceId;
        window.recordingId = res.sid;
        window.uid = res.uid;
        window.AGORA_CLOUD_RECORDING.isCloudRecording = true;

        /* setTimeout(function() {
          // window.resourceId = null;
        }, 1000*60*5); // Agora DOCS: The resource ID is valid for five minutes. */
        cb(null, res);
      } else {
        cb(res, null);
      }
    }).fail(function(err) {
      if (err.responseText && err.responseText.length>0) {
        cb(err.responseText, null);
      } else {
        cb(err, null);
      }
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
      // console.log('Stop:', res);
      window.AGORA_CLOUD_RECORDING.isCloudRecording = false;
      window.recording = res.serverResponse;
      cb(null, res);

    }).fail(function(err) {
      console.error('API Error:', err.responseJSON ? err.responseJSON.errors : err);
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
  },

  updateLayout: function() {
    var params = {
      action: 'cloud_record', // wp ajax action
      sdk_action: 'updateLayout',
      cid: window.channelId,
      cname: window.channelName,
      uid: window.uid,
      resourceId: window.resourceId,
      recordingId: window.recordingId
    };
    // window.AGORA_UTILS.agoraApiRequest(ajax_url, params).done(function(res) {
    //   console.log('Query:', res);

    // }).fail(function(err) {
    //   console.error('API Error:',err);
    // })
  }
}

/* Handle Ghost Mode */

let local_stream_div_id = '#local-video';

function noVideoStreamsDiv(){
  return '<div id="big-no-video-stream" style="display:none"><div id="" class="no-video-overlay text-center"><i class="fas fa-user"></i></div></div>';
}

jQuery(document).ready(function(){

  if(jQuery('body #agora-root #big-no-video-stream').length==0){
    let noStreamDiv = noVideoStreamsDiv();
    jQuery('body #screen-zone').append(noStreamDiv);
  }
});

function showVisibleScreen(){

  let oldClass = jQuery("#screen-users").attr('class');

  console.log("showvisstreams ")
  let total_visible_streams = 0;
  if(jQuery('body #agora-root '+local_stream_div_id).is(":visible")){
    total_visible_streams++;    
    jQuery('body '+local_stream_div_id).css('display', 'inline-flex');
  }
  
  jQuery('body #agora-root .remote-stream-container').each(function(){
    if(jQuery(this).is(":visible")){
      total_visible_streams++;
      jQuery(this).css('display', 'inline-flex');
    }
  });

  if(jQuery("body #agora-root .screenshare-container").length>0){
    total_visible_streams++;
  }

  console.log("hlwtotal_visible_streams", total_visible_streams)

  if(total_visible_streams == 0){
    jQuery("body #big-no-video-stream").show();
  } else {
    jQuery("body #big-no-video-stream").hide();
  }
  let newClass = getScreenUsersClass(total_visible_streams);
  console.log("hlwoldClass", oldClass)
  jQuery("#screen-users").removeClass(oldClass);
  jQuery("#screen-users").addClass(newClass);
}

function getScreenUsersClass(total_visible_streams){
	// let total_visible_streams = 0;

  //   if(jQuery('body '+local_stream_div_id).is(":visible")){
  //     total_visible_streams++;
  //   }
    
	// jQuery('body .remote-stream-container').each(function(){
	// 	if(jQuery(this).is(":visible")){
	// 		total_visible_streams++;
	// 	}
	// });
	
	let countClass = 'screen-users screen-users-'+total_visible_streams.toString();
  if(total_visible_streams == 5 || total_visible_streams == 6) {
		countClass = 'screen-users screen-users-5-6';
	} 
	
  if(total_visible_streams == 7 || total_visible_streams == 8 || total_visible_streams==9) {
		countClass = 'screen-users screen-users-7-9';
	}
	
	if(total_visible_streams == 10 || total_visible_streams == 11 || total_visible_streams==12) {
		countClass = 'screen-users screen-users-9-12';
	}
  console.log("hlwcountClass", countClass)
	return countClass;
}

function handleGhostMode(uid, streamType='local', channelType='communication'){
  if(window.isGhostModeEnabled){
    if(streamType == 'local'){
      console.log("hlwLocal")
      if(channelType == 'broadcast'){
        local_stream_div_id = "#full-screen-video";
      }
      if((!window.localStreams.camera.stream.getAudioTrack() || !window.localStreams.camera.stream.getAudioTrack().enabled)
      && (!window.localStreams.camera.stream.getVideoTrack() || !window.localStreams.camera.stream.getVideoTrack().enabled)
      ){
        jQuery("body "+local_stream_div_id).hide();
      } else {
        jQuery("body "+local_stream_div_id).show();
      }
      //showVisibleScreen();
    }
    else if(streamType == 'remote' && window.remoteStreams[uid]){
      console.log("hlwRemite")
      //console.log("hlwRemoteStream", window.remoteStreams[uid].stream)
      if((window.remoteStreams[uid].stream && (!window.remoteStreams[uid].stream.getAudioTrack() || !window.remoteStreams[uid].stream.getAudioTrack().enabled))
      && (window.remoteStreams[uid].stream && (!window.remoteStreams[uid].stream.getVideoTrack() || !window.remoteStreams[uid].stream.getVideoTrack().enabled))
      ){
        console.log("hlwHideGhost")
        window.AGORA_UTILS.toggleVisibility('#' + uid + '_container', false);
      } else {
        console.log("audioTrackStaastu", window.remoteStreams[uid].stream.getAudioTrack().enabled)
        console.log("audioTrackStaastu", window.remoteStreams[uid].stream.getVideoTrack().enabled)
        console.log("hlwShowGhost", uid)
        window.AGORA_UTILS.toggleVisibility('#' + uid + '_container', true);
      }
      //showVisibleScreen();
    }
    showVisibleScreen();
  }
}
/* End Handle Ghost Mode */

/* Function to set Global colors from admin settings */

function apply_global_colors(){
  const params = {action: 'get_global_colors'};

  window.AGORA_UTILS.agoraApiRequest(ajax_url, params).done(function(res) {
    console.log('Query:', res);
    if(typeof res.global_colors!='undefined' && res.global_colors!=null){
      console.log("glalSettings", res.global_settings)
      const global_colors = res.global_colors;
      if(typeof global_colors.backgroundColorPanels!='undefined' && global_colors.backgroundColorPanels!=""){
        window.panelsBackgroundColor = global_colors.backgroundColorPanels; 
        jQuery('.panel-background-color').css('background-color', window.panelsBackgroundColor);
      }
      if(typeof global_colors.otherButtonsColor!='undefined' && global_colors.otherButtonsColor!=""){
        window.otherButtonsColor = global_colors.otherButtonsColor;
        jQuery('.other-buttons').css({'border': 'none', 'background-color': window.otherButtonsColor});
      }
      if(typeof global_colors.selectedVideoControlsButtonsColor!='undefined' && global_colors.selectedVideoControlsButtonsColor!=""){
        window.selectedVideoControlsButtonsColor = global_colors.selectedVideoControlsButtonsColor;
      }
      if(typeof global_colors.unselectedVideoControlsButtonsColor!='undefined' && global_colors.unselectedVideoControlsButtonsColor!=""){
        window.unselectedVideoControlsButtonsColor = global_colors.unselectedVideoControlsButtonsColor;
        jQuery('.btnIcon:not(.other-buttons)').css('background-color', window.unselectedVideoControlsButtonsColor);
      }
      if(typeof global_colors.backgroundColorVideoMuted!='undefined' && global_colors.backgroundColorVideoMuted!=""){
        window.videoMutedBackgroundColor = global_colors.backgroundColorVideoMuted;
      }
    }
  }).fail(function(err) {
    console.error('API Error:',err);
  })
}

/* Function to handle Mute/Unmute streams by Admin User */
function muteUnmuteRemoteStream(streamRtmUserId, type, status){
  let msgText = 'CONFIRMATION-'+status.toUpperCase()+'-'+type.toUpperCase();
  if((window.canUnmuteForcefully) || (status == 'mute')){ 
    msgText = status.toUpperCase()+'-'+type.toUpperCase();
  }

  let memberId = streamRtmUserId;
    const msg = {
      description: undefined,
      messageType: 'TEXT',
      rawMessage: undefined,
      text: msgText
    }
    try{
      window.AGORA_RTM_UTILS.sendPeerMessage(msg, memberId);
    } catch(e){

  }
}
  
/* Function to get RTM UId from Stream ID */
function generateRTMUidfromStreamId(streamId){
  streamId= parseInt(streamId);
  const numberUID = streamId < 1000 ? streamId + 1000 : streamId;
  return 'x'+ String(numberUID); 
}

/* Function to show Mute/Unmute icons on remote streams for Admin User */
function handleRemoteStreamControlsIcons(streamId){
  const currStreamRTMUserId = generateRTMUidfromStreamId(streamId); 

  let streamAudioIcon = "<i class='fas fa-microphone mute-remote-audio' rel='"+currStreamRTMUserId+"'></i>";
  if(!window.remoteStreams[streamId].stream.getAudioTrack() || !window.remoteStreams[streamId].stream.getAudioTrack().enabled){
    streamAudioIcon = "<i class='fas fa-microphone-slash unmute-remote-audio' rel='"+currStreamRTMUserId+"'></i>";
  }

  let streamVideoIcon = "<i class='fas fa-video mute-remote-video' rel='"+currStreamRTMUserId+"'></i>";
  if(!window.remoteStreams[streamId].stream.getVideoTrack() || !window.remoteStreams[streamId].stream.getVideoTrack().enabled){
    streamVideoIcon = "<i class='fas fa-video-slash unmute-remote-video' rel='"+currStreamRTMUserId+"'></i>";
  }

  if(jQuery('.remote-stream-controls').length>0){
    jQuery('.remote-stream-controls .mute-remote-audio-div').html(streamAudioIcon);
    jQuery('.remote-stream-controls .mute-remote-video-div').html(streamVideoIcon);
  }
}

jQuery(document).ready(function(){

  /* Show Video Controls footer if no pre call device test is enabled */
  // if(!window.pre_call_device_test_enabled || window.agoraMode=='audience'){
  //   jQuery('body .agora-footer').css('display', 'flex');
  // }

  /* Show Mute/Unmute icons on remote streams only to Admin User and mute/unmute functionality */
  if(window.isAdminUser){
    jQuery("body").on("mouseenter", ".remote-stream-container", function(){
      const streamId = jQuery(this).attr('rel'); 

      jQuery(this).append(
        "<div class='remote-stream-controls'>"+
          "<div class='mute-remote-audio-div'></div>"+
          "<div class='mute-remote-video-div'></div>"+
        "</div>"
      )

      handleRemoteStreamControlsIcons(streamId);
    });
    jQuery("body").on("mouseleave", ".remote-stream-container", function(){
      if(jQuery("body .remote-stream-container .remote-stream-controls").length>0){
        jQuery(".remote-stream-controls").remove();
      }
    });

    jQuery("body").on("click", ".mute-remote-audio", function(){
      muteUnmuteRemoteStream(jQuery(this).attr('rel'), 'audio', 'mute');
    });

    jQuery("body").on("click", ".unmute-remote-audio", function(){
      muteUnmuteRemoteStream(jQuery(this).attr('rel'), 'audio', 'unmute');
    });

    jQuery("body").on("click", ".mute-remote-video", function(){
      muteUnmuteRemoteStream(jQuery(this).attr('rel'), 'video', 'mute');
    });

    jQuery("body").on("click", ".unmute-remote-video", function(){
      muteUnmuteRemoteStream(jQuery(this).attr('rel'), 'video', 'unmute');
    });
    
  }
  /* End Mute/Unmute icons on remote streams only to Admin User and mute/unmute functionality */

  apply_global_colors();

  if(winwindow.isSpeakerViewWithRemoteRight){
    /* Handle Pin/Unpin - To pin stream into main view, need to stop the stream and then, start again */
    jQuery("body").on("click", ".remote-stream-main-container", function(){

      let isMainStreamLocal = false; isRightStreamLocal = false;

      /* Handle Main Stream - Stop */
      let mainLargeScreenStreamId = jQuery('.main-screen #main-screen-stream-section div:first-child').attr('id');
      if(mainLargeScreenStreamId == 'local-video' || mainLargeScreenStreamId == 'full-screen-video'){
        isMainStreamLocal = true;
      } else {
        //mainLargeScreenStreamId = jQuery('.main-screen #main-screen-stream-section div:first-child').find('.remote-stream-container').attr('rel');
        mainLargeScreenStreamId = jQuery('.main-screen #main-screen-stream-section div:first-child').attr('id').split('_container')[0];
      }

      if(isMainStreamLocal){ /* If main stream is of local video */
        window.localStreams.camera.stream.stop();
      } else {
        window.remoteStreams[mainLargeScreenStreamId].stream.stop();
        jQuery('#'+mainLargeScreenStreamId+'_container .resume-userclick').remove();
      }
      /* End Handle Main Stream */
      
      /* Handle Right Stream - stop */
      let rightStreamId = jQuery(this).find('div:first-child').attr('id');
      if(rightStreamId == 'local-video' || rightStreamId == 'full-screen-video'){
        isRightStreamLocal = true;
      } else {
        //rightStreamId =jQuery(this).find('.remote-stream-container').attr('rel');
        rightStreamId =jQuery(this).find('div:first').attr('id').split('_container')[0];
      }
      if(isRightStreamLocal){ /* If right side stream is of local video */
        window.localStreams.camera.stream.stop();
      } else {
        window.remoteStreams[rightStreamId].stream.stop();
        jQuery('#'+rightStreamId+'_container .resume-userclick').remove();
      }
      /* End Handle Right Stream */

      /* Store the current main large screen stream id, so that it can be used later when playing right stream */
      let mainLargeScreenStreamIdAsRightStream = mainLargeScreenStreamId; 

      /* Exchange streams positions */
      let currStreamDiv = jQuery(this).find('div:first-child').prop("outerHTML");
      let currMainLargeScreenStream = jQuery('.main-screen #main-screen-stream-section').html();
      jQuery('.main-screen #main-screen-stream-section').html(currStreamDiv);
      jQuery(this).html(currMainLargeScreenStream);
      /* End Exchange streams positions */


      /* Handle Main Stream - play */
      isMainStreamLocal = false;
      mainLargeScreenStreamId = jQuery('.main-screen #main-screen-stream-section div:first-child').attr('id');
      if(mainLargeScreenStreamId == 'local-video' || mainLargeScreenStreamId == 'full-screen-video'){
        isMainStreamLocal = true;
      } else {
        //mainLargeScreenStreamId = jQuery('.main-screen #main-screen-stream-section').find('.remote-stream-container').attr('rel');
        mainLargeScreenStreamId = jQuery('.main-screen #main-screen-stream-section div:first-child').attr('id').split('_container')[0];
      }

      if(isMainStreamLocal) { /* If main stream is of local video */
        window.localStreams.camera.stream.play(mainLargeScreenStreamId);
      } else {
        let remoteStream = window.remoteStreams[mainLargeScreenStreamId].stream;
        
        remoteStream.play('agora_remote_' + mainLargeScreenStreamId, function(err){
          if ((err && err.status !== "aborted") || (err && err.audio && err.audio.status !== "aborted")){
            jQuery('body #' + mainLargeScreenStreamId + '_container').prepend(
              addAudioErrorGesture(mainLargeScreenStreamId)
            )
          }  
          handleGhostMode(mainLargeScreenStreamId, 'remote');
        });
      }
      /* End Handle Main Stream */


      /* Handle Right Stream - play */
      isRightStreamLocal = false;
      rightStreamId = mainLargeScreenStreamIdAsRightStream;
      if(rightStreamId == 'local-video' || rightStreamId == 'full-screen-video'){ /* If right side stream is of local video */
        isRightStreamLocal = true;
      }

      if(isRightStreamLocal) {
        window.localStreams.camera.stream.play(rightStreamId);
      } else {
        let remoteStream = window.remoteStreams[rightStreamId].stream;
        
        remoteStream.play('agora_remote_' + rightStreamId, function(err){
          if ((err && err.status !== "aborted") || (err && err.audio && err.audio.status !== "aborted")){
            jQuery('body #' + rightStreamId + '_container').prepend(
              addAudioErrorGesture(rightStreamId)
            )
          }  
          handleGhostMode(rightStreamId, 'remote');
        });
      }
      /* End Handle Right Stream */

    });

    /* End Handle Pin/Unpin */
  }

});

/* Function to handle background color for Muted Video */
function handleMutedVideoBackgroundColor(streamId=0, type='local'){
  if(window.videoMutedBackgroundColor!=""){
    if(type=='local') {
      jQuery("body #no-local-video").css('background-color', window.videoMutedBackgroundColor);
    } else {
      jQuery('body #' + streamId + '_no-video').css('background-color', window.videoMutedBackgroundColor)
    }
  }
}

function reload(scriptList) {
  scriptList.forEach(x=>{
      loadJs(x.src,x.id);
  })
}

// reload js to render the effect       
function loadJs(file,id)
{
  jQuery("#"+id).remove();
  jQuery("<script >"+"</script>").attr({id:id,src:file,type:'text/javascript'}).appendTo(jQuery('body'));
}

function handleHostJS(){
  /* When a audience user is becoming a host through raise hand, reload the JS */
  if(typeof window.roleFromAudienceToHost!='undefined'){
    var scriptList=[
      {src:jQuery("script#wp-agora-io-agora-deviceTest-js-js").attr('src'),id:"wp-agora-io-agora-deviceTest-js-js"},
      {src:jQuery("script#wp-agora-io-agora-stream-audioErr-js").attr('src'),id:"wp-agora-io-agora-stream-audioErr-js"},
    ];
    reload(scriptList) ; 
  }
}

/* Function to create temp stream for pre-call device test */
async function createTmpCameraStream(uid, hasVideo){
  await handleHostJS();

  const localStream = AgoraRTC.createStream({
    streamID: uid,
    audio: true,
    video: hasVideo,
    screen: false
  });
  localStream.setVideoProfile(window.cameraVideoProfile);

  // The user has granted access to the camera and mic.
  localStream.on("accessAllowed", function() {
    if(window.devices.cameras.length === 0 && window.devices.mics.length === 0) {
      AgoraRTC.Logger.info('[DEBUG] : checking for cameras & mics');
      window.AGORA_UTILS.getCameraDevices();
      window.AGORA_UTILS.getMicDevices();
    }
    AgoraRTC.Logger.info("accessAllowed");
    if(!hasVideo){
      const msg = {
        text: "USER_JOINED_WITHOUT_PERMISSIONS",
        messageType:"TEXT"
      }
      console.log('sending message')
      window.AGORA_RTM_UTILS.sendChannelMessage(msg, cb)
    }
  });
  // The user has denied access to the camera and mic.
  localStream.on("accessDenied", function() {
    AgoraRTC.Logger.warning("accessDenied");
  });

  localStream.init(function() {
    
    AgoraRTC.Logger.info('getUserMedia successfully');
    if(window.channel_type == 'broadcast'){
      localStream.play('full-screen-video'); // play the local stream on the main div
    } else{
      localStream.play('local-video'); // play the local stream on the main div
    }

    window.localStreams.tmpCameraStream = localStream; // keep track of the camera stream for later

    jQuery('#buttons-container').fadeIn();
  }, function (err) {
    AgoraRTC.Logger.error('[ERROR] : getUserMedia failed', err);

    if (err.msg==='NotAllowedError') {
      const msg = {
        text: "USER_JOINED_WITHOUT_PERMISSIONS**"+uid,
        messageType:"TEXT"
      }
      window.AGORA_RTM_UTILS.sendChannelMessage(msg)
      window.AGORA_COMMUNICATION_UI.enableExit()
      window.AGORA_UTILS.showPermissionsModal()
    }
  });
}

/* Handle raise hand requests pop-up */

/* Function to get the HTML content to be added in Raise Hand Requests Pop-up */
function raiseHandRequestsContent(){
  let html = "<div>";
  let requests = window.raiseHandRequests;
  if(Object.keys(window.raiseHandRequests).length == 0){
    html+="<div class='request-row'>No request!!</div>";
  } else{
    Object.keys(requests).forEach(function(key) {
      html+="<div class='request-row' id='request-row-"+key+"'><div class='user-details'>"+requests[key].userId+"</div><button class='accept-raise-hand' id='"+key+"'>Accept</button>"+"<button class='reject-raise-hand' id='"+key+"'>Reject</button>"+"</div>";
    });
  }
  html+="</div>";
  return html;
}

/* Function to change the HTML content after Raise hand Request is processed */
function handleRequestContentAfterProcess(memberId){
  delete window.raiseHandRequests[memberId];
  jQuery("body #request-row-"+memberId).remove();
  let totalRequests = Object.keys(window.raiseHandRequests).length;
  if(totalRequests == 0){ totalRequests = '';  }
  jQuery("body .raise-hand-requests #total-requests").html(totalRequests);
}

jQuery(document).ready(function(){
  
  jQuery("body").on("click", ".raise-hand-requests button", function(){
    jQuery("body").find("#view-raise-hand-requests-modal #raise-hand-requests-list").html(raiseHandRequestsContent());
    jQuery('#view-raise-hand-requests-modal').modal('toggle');
  });

  jQuery("body").on("click", ".reject-raise-hand", function(){
    let memberId = jQuery(this).attr('id');
    const msg = {
      description: undefined,
      messageType: 'TEXT',
      rawMessage: undefined,
      text: 'RAISE-HAND-REJECTED'
    }
    try{
      window.AGORA_RTM_UTILS.sendPeerMessage(msg, memberId);
      handleRequestContentAfterProcess(memberId);
    } catch(e){

    }
  });
  
  jQuery("body").on("click", ".accept-raise-hand", function(){
    let memberId = jQuery(this).attr('id');
    const msg = {
      description: undefined,
      messageType: 'TEXT',
      rawMessage: undefined,
      text: 'RAISE-HAND-ACCEPTED'
    }
    try{
      window.AGORA_RTM_UTILS.sendPeerMessage(msg, memberId);
      handleRequestContentAfterProcess(memberId);
    } catch(e){

    }
  });

});

/* End Handle raise hand requests pop-up */


/* Handle - change User Role - From Raise hand in Broadcast mode */
function joinAsHost(){
  var params = {
    action: 'load_host_view', // wp ajax action
    channel_id: window.channelId
  };

  /* Remove Previous RTM Event Listeners when joining from audience to host */
  window.removeEventListener('agora.rtm_init', loadChatApp);
  window.removeEventListener('agora.rtmMessageFromChannel', receiveRTMMessage);

  /* Remove Previous Files of audience */
  if(jQuery("script#wp-agora-io-chat-js").length>0){
    jQuery("script#wp-agora-io-chat-js").remove();
  }

  if(jQuery("script#wp-agora-raise-hand-js").length>0){
    jQuery("script#wp-agora-raise-hand-js").remove();
  }

  jQuery("link#wp-agora-io-chat-fab-css").remove();

  window.AGORA_UTILS.agoraApiRequest(ajax_url, params).done(function(res) {
    console.log("afterAjaxSuccess")

    let mainElm = jQuery('#agora-root').parent();
    jQuery('#agora-root').remove();
    mainElm.html(res);
    apply_global_colors();

  }).fail(function(err) {
    console.error('API Error:', err.responseJSON ? err.responseJSON.errors : err);
  })
}
/* End Handle - change User Role - From Raise hand or user limit in Communication Mode */

/* Function that will be run on rtm init event listener */
function loadChatApp() {
    const headTag = document.head || document.getElementsByTagName("head")[0];

    const chatStyles = document.createElement("link");
    chatStyles.rel = "stylesheet";
    chatStyles.href = `${window.agora_base_url}css/chat-fab.css`;
    chatStyles.setAttribute('id', 'wp-agora-io-chat-fab-css');
    headTag.appendChild(chatStyles);

    const arleneLib = document.createElement("script")
    arleneLib.type = "text/javascript";
    arleneLib.src = `${window.agora_base_url}js/chat.js`;
    arleneLib.setAttribute('id', 'wp-agora-io-chat-js');
    arleneLib.async = true;
    headTag.appendChild(arleneLib);
  }

  /* End Function that will be run on rtm init event listener */

  /* Function that will be run on rtm channel message event listener */
  function receiveRTMMessage(evt) {

    if (evt.detail && evt.detail.text) {

      /* Handle Raise Hand Request */
      // if(evt.detail.text.indexOf('CANCEL-RAISE-HAND-')===0){
      //   let senderId = evt.detail.senderId;
      //   delete window.raiseHandRequests[senderId];
      //   let totalRequests = Object.keys(window.raiseHandRequests).length;
      //   if(totalRequests == 0){ totalRequests = '';  }
      //   jQuery("body .raise-hand-requests #total-requests").html(totalRequests);
      // }
      // else if (evt.detail.text.indexOf('RAISE-HAND-')===0) {
      //   let senderRTCId = evt.detail.text.split('RAISE-HAND-')[1];
      //   let senderId = evt.detail.senderId;
      //   window.raiseHandRequests[senderId] = {
      //     'userId': senderRTCId,
      //     'status' : 0
      //   }
      //   jQuery("body .raise-hand-requests #total-requests").html(Object.keys(window.raiseHandRequests).length);
      // }
      /* End Handle Raise Hand Request */
      if (evt.detail.text.indexOf('USER_JOINED_WITHOUT_')===0) {
        const pos = evt.detail.text.indexOf('**') + 2;
        const uid = evt.detail.text.substring(pos)

        const titleModal = "New user joined"
        let contentModal = "A new guest user has joined without video";
        

        window.AGORA_UTILS.agora_getUserAvatar(uid, function getUserAvatar(avatarData) {
          if (avatarData && avatarData.user) {
            contentModal = contentModal.replace('A new guest user', avatarData.user.display_name)

            // make sure the name is capitalized
            contentModal = contentModal.charAt(0).toUpperCase() + contentModal.slice(1)
          }

          document.getElementById('agora-toast-title').innerText = titleModal
          document.getElementById('agora-toast-body').innerText = contentModal

          jQuery('.toast').toast('show')
        });
      } else if(evt.detail.text.indexOf(`CHAT-FILE${window.AGORA_CHAT.TOKEN_SEP}`)==0){
				const msgData = evt.detail.text;
				window.AGORA_RTM_UTILS.addRemoteMsg(evt.detail.senderId, msgData)
			}
			else if (evt.detail.text.indexOf(`CHAT${window.AGORA_CHAT.TOKEN_SEP}`)===0) {
				const msgData = evt.detail.text.substring(6);
				window.AGORA_RTM_UTILS.addRemoteMsg(evt.detail.senderId, msgData)
			} else if (evt.detail.text.indexOf(`CHAT-JOIN${window.AGORA_CHAT.TOKEN_SEP}`)===0) {
				const msgData = evt.detail.text.substring(11);
				window.AGORA_CHAT.showUserNotify(msgData, 'join');
			} else if (evt.detail.text.indexOf(`CHAT-LEAVE${window.AGORA_CHAT.TOKEN_SEP}`)===0) {
				const msgData = evt.detail.text.substring(12);
				window.AGORA_CHAT.showUserNotify(msgData, 'leave');
			}
    }
}
/* End Function that will be run on rtm channel message event listener */

/* Object to handle the requested permissions */
let requestedPermission = {
  audio: false,
  video: false
}

/* Function to set content in requested permission popup */
function getPermConfirmationContent(type){
  console.log("hlwrequestedPermission", requestedPermission)
  if(requestedPermission.audio && requestedPermission.video){
    type = 'Audio/Video';
  } else if(requestedPermission.video){
    type = 'Video';
  } else if(requestedPermission.audio){
    type = 'Audio';
  }
  let html = "<div class='permission-confirmation-section'>";
  html+="<div class='permission-text'>Do you want to allow "+type+" ? </div>";
  html+="<div class='actions'><button class='accept' onClick='allowAccess()'>Allow</button><button class='deny' onClick='denyAccess()'>Deny</button></div>";
  html+= "</div>";
  return html;
}

/* Reset handle requested permissions object on modal close */
jQuery(document).ready(function(){
  jQuery("#view-allow-disallow-permission-modal").on('hidden.bs.modal', function (e) {
    requestedPermission = {
      audio: false,
      video: false
    }
  });
});

window.addEventListener('agora.rtmMessageFromPeer', receivePeerRTMMessage);

/* Function to run when user allow the request */
function allowAccess(){
  if(requestedPermission.video){
    jQuery("#video-icon").trigger('click');
  } 
  if(requestedPermission.audio){
    jQuery("#mic-btn").trigger('click');
  }
  jQuery("#view-allow-disallow-permission-modal").modal('toggle');
}

/* Function to run when user deny the request */
function denyAccess(){
  jQuery("#view-allow-disallow-permission-modal").modal('toggle');
}

/* Function that will be run on rtm peer message event listener */
async function receivePeerRTMMessage(evt) {
  console.log("hlwPeerMsg", evt.detail.text)
  if (evt.detail && evt.detail.text) {

    /* Handle Raise Hand Request - Response */

    /* Handle raise Hand Request */
    if(evt.detail.text.indexOf('CANCEL-RAISE-HAND-REQUEST-')===0){
      let senderId = evt.detail.senderId;
      delete window.raiseHandRequests[senderId];
      let totalRequests = Object.keys(window.raiseHandRequests).length;
      if(totalRequests == 0){ totalRequests = '';  }
      jQuery("body .raise-hand-requests #total-requests").html(totalRequests);
    }
    else if (evt.detail.text.indexOf('RAISE-HAND-REQUEST-')===0) {
      let senderRTCId = evt.detail.text.split('RAISE-HAND-REQUEST-')[1];
      let senderId = evt.detail.senderId;
      window.raiseHandRequests[senderId] = {
        'userId': senderRTCId,
        'status' : 0
      }
      jQuery("body .raise-hand-requests #total-requests").html(Object.keys(window.raiseHandRequests).length);
    }

    /* Handle raise Hand Response */
    /* If Raise hand Request is Rejected */
    else if(evt.detail.text.indexOf('RAISE-HAND-REJECTED')===0){
      console.log("Raise hand Request Rejected")
      window.AGORA_AUDIENCE.raiseHandRequestRejected();
    } 

    /* If Raise hand Request is Accepted */
    else if(evt.detail.text.indexOf('RAISE-HAND-ACCEPTED')===0){
      console.log("Raise hand Request Accepted")
      await window.AGORA_AUDIENCE.agoraLeaveChannel();
      joinAsHost();
    }

    /* Unmute audio with confirmation from Admin User */
    else if(evt.detail.text.indexOf('CONFIRMATION-UNMUTE-AUDIO')===0){
      requestedPermission['audio'] = true;
      jQuery('#view-allow-disallow-permission-modal #allow-disallow-permissions-content').html(getPermConfirmationContent('audio'));
      jQuery('#view-allow-disallow-permission-modal').modal('show')
    }
    /* Muted audio from Admin User */
    else if(evt.detail.text.indexOf('UNMUTE-AUDIO')===0){
      jQuery("#mic-btn").trigger('click');
    }

    /* Mute audio from Admin User */
    else if(evt.detail.text.indexOf('MUTE-AUDIO')===0){
      jQuery("#mic-btn").trigger('click');
    }

    /* Unmute video with confirmation from Admin User */
    else if(evt.detail.text.indexOf('CONFIRMATION-UNMUTE-VIDEO')===0){
      requestedPermission['video'] = true;
      jQuery('#view-allow-disallow-permission-modal #allow-disallow-permissions-content').html(getPermConfirmationContent('video'));
      jQuery('#view-allow-disallow-permission-modal').modal('show')
    }
    /* Unmute video from Admin User */
    else if(evt.detail.text.indexOf('UNMUTE-VIDEO')===0){
      console.log("Unmute Video Accepted")
      jQuery("#video-icon").trigger('click');
    }

    /* Mute video from Admin User */
    else if(evt.detail.text.indexOf('MUTE-VIDEO')===0){
      jQuery("#video-icon").trigger('click');
    }

  }
}
/* End Function that will be run on rtm peer message event listener */