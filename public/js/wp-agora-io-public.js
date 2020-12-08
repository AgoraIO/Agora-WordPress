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
      case 10:
      case 11:
      case 12:
        countClass = '9-12';
        break;
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
    });

    window.agoraClient.on("unmute-audio", function unmuteAudio(evt) {
      window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_mute', false);
    });

    // show user icon whenever a remote has disabled their video
    window.agoraClient.on("mute-video", function muteVideo(evt) {
      const remoteId = evt.uid;
      // if the main user stops their video select a random user from the list
      window.AGORA_UTILS.toggleVisibility('#' + remoteId + '_no-video', true);
    });

    agoraClient.on("unmute-video", function unmuteVideo(evt) {
      window.AGORA_UTILS.toggleVisibility('#' + evt.uid + '_no-video', false);
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
