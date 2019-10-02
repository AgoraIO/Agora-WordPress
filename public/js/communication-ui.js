

// UI buttons
function enableUiControls(localStream) {

  jQuery("#mic-btn").prop("disabled", false);
  jQuery("#video-btn").prop("disabled", false);
  jQuery("#screen-share-btn").prop("disabled", false);
  jQuery("#exit-btn").prop("disabled", false);

  jQuery("#mic-btn").click(function(){
    toggleMic(localStream);
  });

  jQuery("#video-btn").click(function(){
    toggleVideo(localStream);
  });

  jQuery("#screen-share-btn").click(function() {
    toggleScreenShareBtn(); // set screen share button icon
    var loaderIcon = jQuery(this).find('.spinner-border');
    var closeIcon = jQuery('#screen-share-icon');
    loaderIcon.show();
    closeIcon.hide();

    var toggleLoader = function(err, next) {
      loaderIcon.hide();
      closeIcon.show();
      // TODO: is not needed but I could capture the callback result here...
    }

    jQuery("#screen-share-btn").prop("disabled",true); // disable the button on click
    if(window.screenShareActive){
      stopScreenShare(toggleLoader);
    } else {
      initScreenShare(toggleLoader);
    }
  });

  jQuery("#exit-btn").click(function(){
    console.log("so sad to see you leave the channel");
    leaveChannel(); 
  });

  jQuery('#rejoin-btn').click(rejoinChannel);

  // keyboard listeners 
  jQuery(document).keypress(function(e) {
    switch (e.key) {
      case "m":
        console.log("squick toggle the mic");
        toggleMic(localStream);
        break;
      case "v":
        console.log("quick toggle the video");
        toggleVideo(localStream);
        break; 
      case "s":
        console.log("initializing screen share");
        toggleScreenShareBtn(); // set screen share button icon
        jQuery("#screen-share-btn").prop("disabled",true); // disable the button on click
        if(screenShareActive){
          stopScreenShare();
        } else {
          initScreenShare(); 
        }
        break;  
      case "q":
        console.log("so sad to see you quit the channel");
        leaveChannel(); 
        break;   
      default:  // do nothing
    }

    // (for testing) 
    if(e.key === "r") { 
      window.history.back(); // quick reset
    }
  });
}

function toggleBtn(btn){
  btn.toggleClass('btn-dark').toggleClass('btn-danger');
}

function toggleVisibility(elementID, visible) {
  if (visible) {
    jQuery(elementID).attr("style", "display:block");
  } else {
    jQuery(elementID).attr("style", "display:none");
  }
}

function toggleMic(localStream) {
  toggleBtn(jQuery("#mic-btn")); // toggle button colors
  jQuery("#mic-icon").toggleClass('fa-microphone').toggleClass('fa-microphone-slash'); // toggle the mic icon
  if (jQuery("#mic-icon").hasClass('fa-microphone')) {
    localStream.unmuteAudio(); // enable the local mic
    toggleVisibility("#mute-overlay", false); // hide the muted mic icon
  } else {
    localStream.muteAudio(); // mute the local mic
    toggleVisibility("#mute-overlay", true); // show the muted mic icon
  }
}

function toggleVideo(localStream) {
  toggleBtn(jQuery("#video-btn")); // toggle button colors
  jQuery("#video-icon").toggleClass('fa-video').toggleClass('fa-video-slash'); // toggle the video icon
  if (jQuery("#video-icon").hasClass('fa-video')) {
    localStream.unmuteVideo(); // enable the local video
    toggleVisibility("#no-local-video", false); // hide the user icon when video is enabled
    logCameraDevices();
  } else {
    localStream.muteVideo(); // disable the local video
    toggleVisibility("#no-local-video", true); // show the user icon when video is disabled
  }
}

function logCameraDevices() {
  console.log("Checking for Camrea Devices.....")
  AgoraRTC.getDevices (function(devices) {
    var devCount = devices.length;
    var id = devices[0].deviceId;
    console.log("getDevices: " + JSON.stringify(devices));
  });

  agoraClient.getCameras (function(cameras) {
    var devCount = cameras.length;
    var id = cameras[0].deviceId;
    console.log("getCameras: " + JSON.stringify(cameras));
  });
}


function calculateVideoScreenSize() {
  var container = jQuery('#full-screen-video');
  console.log('Video SIZE:', container.outerWidth());
  var size = getSizeFromVideoProfile();

  // https://math.stackexchange.com/a/180805
  var newHeight = container.outerWidth() * size.height / size.width;
  container.outerHeight(newHeight);
}

function getSizeFromVideoProfile() {
  // https://docs.agora.io/en/Interactive%20Broadcast/videoProfile_web?platform=Web#video-profile-table
  switch(window.cameraVideoProfile) {
    case '480p_8':
    case '480p_9': return { width: 848, height: 480 };
    case '720p':
    case '720p_1':
    case '720p_2':
    case '720p_3': return { width: 1280, height: 720 };
    case '720p_6': return { width: 960, height: 720 };
    case '1080p':
    case '1080p_1':
    case '1080p_2':
    case '1080p_3':
    case '1080p_5': return { width: 1920, height: 1080 };
  }
}

// Ajax simple requests
function agoraApiRequest(endpoint_url, endpoint_data) {
  var ajaxRequestParams = {
    method: 'POST',
    url: endpoint_url,
    data: endpoint_data
  };
  return jQuery.ajax(ajaxRequestParams)
}
