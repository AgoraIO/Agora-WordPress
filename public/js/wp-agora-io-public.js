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



function changeStreamSource (deviceIndex, deviceType) {
  AgoraRTC.Logger.info('Switching stream sources for: ' + deviceType);
  var deviceId;
  var existingStream = false;
  
  if (deviceType === "video") {
    deviceId = window.devices.cameras[deviceIndex].deviceId
  }

  if(deviceType === "audio") {
    deviceId = window.devices.mics[deviceIndex].deviceId;
  }

  window.localStreams.camera.stream.switchDevice(deviceType, deviceId, function(){
    AgoraRTC.Logger.info('successfully switched to new device with id: ' + JSON.stringify(deviceId));
    // set the active device ids
    if(deviceType === "audio") {
      window.localStreams.camera.micId = deviceId;
    } else if (deviceType === "video") {
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
    cameras.forEach(function(camera, i){
      var name = camera.label.split('(')[0];
      var optionId = 'camera_' + i;
      var deviceId = camera.deviceId;
      if(i === 0 && window.localStreams.camera.camId === ''){
        window.localStreams.camera.camId = deviceId;
      }
      jQuery('#camera-list').append('<a class="dropdown-item" id="' + optionId + '">' + name + '</a>');
    });
    jQuery('#camera-list a').click(function(event) {
      var index = event.target.id.split('_')[1];
      changeStreamSource (index, "video");
    });
  });
}

function getMicDevices() {
  AgoraRTC.Logger.info("Checking for Mic window.devices.....")
  window.agoraClient.getRecordingDevices(function(mics) {
    window.devices.mics = mics; // store mics array
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
      jQuery('#mic-list').append('<a class="dropdown-item" id="' + optionId + '">' + name + '</a>');
    }); 
    jQuery('#mic-list a').click(function(event) {
      var index = event.target.id.split('_')[1];
      changeStreamSource (index, "audio");
    });
  });
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
    // if(!jQuery(btn).hasClass('btn-dark') && !jQuery(btn).hasClass('btn-danger')){
    //   jQuery(btn).css('background-color', window.selectedVideoControlsButtonsColor);
    //   // jQuery(btn).hover(function(){
    //   //   jQuery(this).css('background-color', window.selectedVideoControlsButtonsColor);
    //   // });
    //   // jQuery(btn).mouseleave(function(){
    //   //   jQuery(this).css('background-color', window.unselectedVideoControlsButtonsColor);
    //   // });
    // } else {
    //   jQuery(btn).css('background-color', window.unselectedVideoControlsButtonsColor);
    // }

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
      el.style.display = visible ? "block" : "none";
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

  deleteRemoteStream: function(streamId) {
    window.remoteStreams[streamId].stop(); // stop playing the feed
    delete window.remoteStreams[streamId]; // remove stream from list
    const remoteContainerID = '#' + streamId + '_container';
    jQuery(remoteContainerID).empty().remove();
  },

  setupAgoraListeners: function() {

    // show mute icon whenever a remote has muted their mic
    window.agoraClient.on("mute-audio", function muteAudio(evt) {
      window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_mute', true);
      handleMutedVideoBackgroundColor(evt.uid, 'remote');
      handleGhostMode(evt.uid, 'remote');
    });

    window.agoraClient.on("unmute-audio", function unmuteAudio(evt) {
      window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_mute', false);
      handleGhostMode(evt.uid, 'remote');
    });

    // show user icon whenever a remote has disabled their video
    window.agoraClient.on("mute-video", function muteVideo(evt) {
      const remoteId = evt.uid;
      // if the main user stops their video select a random user from the list
      window.AGORA_UTILS.toggleVisibility('#' + remoteId + '_no-video', true);
      handleMutedVideoBackgroundColor(evt.uid, 'remote');
      handleGhostMode(evt.uid, 'remote');
    });

    agoraClient.on("unmute-video", function unmuteVideo(evt) {
      window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_no-video', false);
      handleGhostMode(evt.uid, 'remote');
    });

    // remove the remote-container when a user leaves the channel
    window.agoraClient.on("peer-leave", function peerLeave(evt) {
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
      }

      if (window.AGORA_CLOUD_RECORDING.isCloudRecording) {
        window.AGORA_CLOUD_RECORDING.updateLayout();
      }
    });


    // connect remote streams
    window.agoraClient.on('stream-added', function streamAdded(evt) {
      const stream = evt.stream;
      const streamId = stream.getId();
      AgoraRTC.Logger.info("new stream added: " + streamId);

      // Check if the stream is the local screen
      if (streamId != window.localStreams.screen.id) {
        AgoraRTC.Logger.info('subscribe to remote stream:' + streamId);
        // Subscribe to the stream.
        agoraClient.subscribe(stream, function (err) {
          AgoraRTC.Logger.error("[ERROR] : subscribe stream failed", err);
        });
      } else {
        // show this stream on main screen
      }
    });

    window.agoraClient.on('stream-subscribed', function streamSubscribed(evt) {
      var remoteStream = evt.stream;
      var remoteId = remoteStream.getId();
      window.remoteStreams[remoteId] = remoteStream;
      // console.log('Stream subscribed:', remoteId);

      AgoraRTC.Logger.info("Subscribe remote stream successfully: " + remoteId);

      const isInjectedStream = window.injectedStreamURL && window.injectedStreamURL!=="";
      if (window.screenshareClients[remoteId] || isInjectedStream) {
        // this is a screen share stream:
        console.log('Screen stream arrived:');
        window.AGORA_SCREENSHARE_UTILS.addRemoteScreenshare(remoteStream);

        if (isInjectedStream) {
          window.AGORA_BROADCAST_UI.toggleCaptureStreamBtn(null, 'started');
        }
      } else {
        // show new stream on screen:
        window.AGORA_UTILS.addRemoteStreamView(remoteStream);

        // always add 1 due to the remote streams + local user
        const usersCount = Object.keys(window.remoteStreams).length + 1
        window.AGORA_UTILS.updateUsersCounter(usersCount);
      }

      if (window.AGORA_CLOUD_RECORDING.isCloudRecording) {
        window.AGORA_CLOUD_RECORDING.updateLayout();
      }
    });

    // Listener for Agora RTM Events
    window.addEventListener('agora.rtmMessageFromChannel', function receiveRTMMessage(evt) {
      if (evt.detail && evt.detail.text) {
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
        }
      }
    })
  },

  // REMOTE STREAMS UI
  addRemoteStreamView: function(remoteStream) {
    const streamId = remoteStream.getId();
    console.log('Adding remote to main view:', streamId);
    // append the remote stream template to #remote-streams
    const streamsContainer = jQuery('#screen-users');

    /* In case if Audience in broadcast channel*/
    if(typeof window.allStreams=='undefined'){
      console.log("setattStreamsVariable")
      window.allStreams = [];
    }
    window.allStreams.push(remoteStream);

    // avoid duplicate users in case there are errors removing old users and rejoining
    const old = streamsContainer.find(`#${streamId}_container`)
    if (old && old[0]) { old[0].remove() }

    streamsContainer.append(
      jQuery('<div/>', {'id': streamId + '_container',  'class': 'user remote-stream-container'}).append(
        jQuery('<div/>', {'id': streamId + '_mute', 'class': 'mute-overlay'}).append(
            jQuery('<i/>', {'class': 'fas fa-microphone-slash'})
        ),
        jQuery('<div/>', {'id': streamId + '_no-video', 'class': 'no-video-overlay text-center'}).append(
          jQuery('<i/>', {'class': 'fas fa-user'})
        ),
        jQuery('<div/>', {'id': 'agora_remote_' + streamId, 'class': 'remote-video'})
      )
    );

    remoteStream.play('agora_remote_' + streamId, function(err){
      if (err && err.status !== "aborted"){
        console.log('Remote stream:' + streamId + ' failed to autoplay. Playing muted. Tap <video> container to enable audio.' );
        remoteStream.stop();
        window.AGORA_UTILS.toggleVisibility('#' + streamId + '_mute', true);
        remoteStream.play('agora_remote_' + streamId, { muted: true });
        // The playback fails. Guide the user to resume the playback by clicking.            
        document.getElementById(streamId + '_container').onclick = playWithAudio; 
        function playWithAudio() {
          console.log('Attempting to play remote stream:' + streamId + ' with audio after user interaction' );
          remoteStream.stop();
          window.AGORA_UTILS.toggleVisibility('#' + streamId + '_mute', false);
          remoteStream.play('agora_remote_' + streamId, { muted: false });
          document.getElementById(streamId + '_container').removeEventListener('click', playWithAudio)
        }     
      }

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
  }
}


window.AGORA_CLOUD_RECORDING = {
  isCloudRecording: false,

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
window.isGhostModeEnabled = false;

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
  console.log("showvisstreams ")
  let total_visible_streams = 0;
  if(jQuery('body '+local_stream_div_id).is(":visible")){
    total_visible_streams++;    
    jQuery('body '+local_stream_div_id).css('display', 'inline-flex');
  }
  
  jQuery('body .remote-stream-container').each(function(){
  if(jQuery(this).is(":visible")){
      total_visible_streams++;
    jQuery(this).css('display', 'inline-flex');
  }
  });
  if(total_visible_streams == 0){
      jQuery("body #big-no-video-stream").show();
  } else {
      jQuery("body #big-no-video-stream").hide();
  }
}

function getScreenUsersClass(){
	let total_visible_streams = 0;

    if(jQuery('body '+local_stream_div_id).is(":visible")){
      total_visible_streams++;
    }
    
	jQuery('body .remote-stream-container').each(function(){
		if(jQuery(this).is(":visible")){
			total_visible_streams++;
		}
	});
	
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
	return countClass;
}

function handleGhostMode(uid, streamType='local'){
  if(window.isGhostModeEnabled){
    console.log("hnjiGhostMode",streamType)
    let oldClass = jQuery("#screen-users").attr('class');
    if(streamType == 'local'){
      if((!window.localStreams.camera.stream.getAudioTrack() || !window.localStreams.camera.stream.getAudioTrack().enabled)
      && (!window.localStreams.camera.stream.getVideoTrack() || !window.localStreams.camera.stream.getVideoTrack().enabled)
      ){
        jQuery("body "+local_stream_div_id).hide();
      } else {
        jQuery("body "+local_stream_div_id).show();
      }
      showVisibleScreen();
    }
    else if(streamType == 'remote'){
      console.log("hlwRemoteStream", window.remoteStreams[uid])
      if((!window.remoteStreams[uid].getAudioTrack() || !window.remoteStreams[uid].getAudioTrack().enabled)
      && (!window.remoteStreams[uid].getVideoTrack() || !window.remoteStreams[uid].getVideoTrack().enabled)
      ){
        window.AGORA_UTILS.toggleVisibility('#' + uid + '_container', false);
      } else {
        window.AGORA_UTILS.toggleVisibility('#' + uid + '_container', true);
      }
      showVisibleScreen();
    }
    let newClass = getScreenUsersClass();
    jQuery("#screen-users").removeClass(oldClass);
    jQuery("#screen-users").addClass(newClass);
  }
}
/* End Handle Ghost Mode */

/* Handle Active Speaker */
jQuery(document).ready(function(){
  const THRESHOLD_AUDIO_LEVEL = 0.1;
  setInterval(() => {

    /* Active speaker condition will work when there are 2 or more than 2 streams */
    if(typeof window.allStreams!='undefined' && window.allStreams.length>1){

      /* Create array to manage the streams queue according to volume  */
      let talkingStreamsQueue = [];

      window.allStreams.forEach((item, key) => {
        let obj = {};
        if(item.getAudioLevel()>0){
          let audioLevel = item.getAudioLevel().toFixed(3);
          obj[item.getId()] = audioLevel;
          talkingStreamsQueue.push({id: parseInt(item.getId()), volume: parseFloat(audioLevel)});
        }
      });

      talkingStreamsQueue.sort((a, b) => b.volume - a.volume);

      let activeSpeakerStreamId = 0;

      if( talkingStreamsQueue.length>0 && talkingStreamsQueue[0].volume > THRESHOLD_AUDIO_LEVEL ) {
        activeSpeakerStreamId = talkingStreamsQueue[0].id;
      }

      if(activeSpeakerStreamId == 0){
        jQuery('.activeSpeaker').removeClass('activeSpeaker');
      } else {
        jQuery('body #' + activeSpeakerStreamId + '_container').addClass('activeSpeaker');
      }
    }
    
  }, 300);
});
/* End Handle Active Speaker */

/* Function to set Global colors from admin settings */
jQuery(document).ready(function(){

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