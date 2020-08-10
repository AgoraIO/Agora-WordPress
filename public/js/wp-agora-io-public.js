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
  }, function(){
    AgoraRTC.Logger.error('failed to switch to new device with id: ' + JSON.stringify(deviceId));
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
    if (visible) {
      jQuery(elementID).attr("style", "display:block");
    } else {
      jQuery(elementID).attr("style", "display:none");
    }
  },

  agora_getUserAvatar: function (user_id, cb) {
    var uid = String(user_id).substring(3);
    console.log('Real WP user ID:', uid)
    var params = {
      action: 'get_user_avatar', // wp ajax action
      uid, // needed to get the avatar from the WP user
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
      case 4:
        countClass = '3-4';
        break;
      case 5:
      case 6:
        countClass = '5-6';
        break;
      case 7:
      case 8:
        countClass = '7-8';
        break;
      case 9: case 10:
      case 11: case 12:
        countClass = '9-12';
        break;
    }

    // Update users class to keep layout organized
    document.getElementById('screen-users').classList = "screen-users screen-users-" + countClass;
  }
}