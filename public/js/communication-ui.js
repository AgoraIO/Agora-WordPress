
window.AGORA_COMMUNICATION_UI = {
// UI buttons
  agoraEnableUiControls: function (localStream) {

    jQuery("#mic-btn").prop("disabled", false);
    jQuery("#video-btn").prop("disabled", false);
    jQuery("#screen-share-btn").prop("disabled", false);
    jQuery("#exit-btn").prop("disabled", false);

    jQuery("#mic-btn").click(function(){
      agoraToggleMic(localStream);
    });

    jQuery("#video-btn").click(function(){
      agoraToggleVideo(localStream);
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
      agoraLeaveChannel(); 
    });

    jQuery('#rejoin-btn').click(rejoinChannel);

    // keyboard listeners 
    jQuery(document).keypress(function(e) {
      switch (e.key) {
        case "m":
          console.log("squick toggle the mic");
          agoraToggleMic(localStream);
          break;
        case "v":
          console.log("quick toggle the video");
          agoraToggleVideo(localStream);
          break; 
        /* case "s":
          console.log("initializing screen share");
          toggleScreenShareBtn(); // set screen share button icon
          jQuery("#screen-share-btn").prop("disabled",true); // disable the button on click
          if(screenShareActive){
            stopScreenShare();
          } else {
            initScreenShare(); 
          }
          break;  */
        case "q":
          console.log("so sad to see you quit the channel");
          agoraLeaveChannel(); 
          break;   
        default:  // do nothing
      }

      // (for testing) 
      if(e.key === "r") { 
        // window.history.back(); // quick reset
      }
    });
  },

  agoraToggleBtn: function (btn){
    btn.toggleClass('btn-dark').toggleClass('btn-danger');
  },

  agora_toggleVisibility: function (elementID, visible) {
    if (visible) {
      jQuery(elementID).attr("style", "display:block");
    } else {
      jQuery(elementID).attr("style", "display:none");
    }
  },

  agoraToggleMic: function (localStream) {
    agoraToggleBtn(jQuery("#mic-btn")); // toggle button colors
    jQuery("#mic-icon").toggleClass('fa-microphone').toggleClass('fa-microphone-slash'); // toggle the mic icon
    if (jQuery("#mic-icon").hasClass('fa-microphone')) {
      localStream.unmuteAudio(); // enable the local mic
      agora_toggleVisibility("#mute-overlay", false); // hide the muted mic icon
    } else {
      localStream.muteAudio(); // mute the local mic
      agora_toggleVisibility("#mute-overlay", true); // show the muted mic icon
    }
  },

  agoraToggleVideo: function (localStream) {
    agoraToggleBtn(jQuery("#video-btn")); // toggle button colors
    jQuery("#video-icon").toggleClass('fa-video').toggleClass('fa-video-slash'); // toggle the video icon
    if (jQuery("#video-icon").hasClass('fa-video')) {
      localStream.unmuteVideo(); // enable the local video
      agora_toggleVisibility("#no-local-video", false); // hide the user icon when video is enabled
      logCameraDevices();
    } else {
      localStream.muteVideo(); // disable the local video
      agora_toggleVisibility("#no-local-video", true); // show the user icon when video is disabled
    }
  },

  logCameraDevices: function () {
    console.log("Checking for Camera Devices.....")
    AgoraRTC.getDevices (function(devices) {
      var devCount = devices.length;
      if (devCount>0) {
        var id = devices[0].deviceId;
        // console.log('Device:', devices[0])
        // console.log("getDevices: " + JSON.stringify(devices));
      }
    });

    agoraClient.getCameras(function(cameras) {
      var devCount = cameras.length;
      var id = cameras[0].deviceId;
      console.log("getCameras: " + JSON.stringify(cameras));
    });
  },


  rejoinChannel: function () {
    var thisBtn = jQuery(this);
    if(!thisBtn.prop('disabled')) {
      thisBtn.prop("disabled", true);
      thisBtn.find('.spinner-border').show();
      joinChannel(window.channelName);
    }
  },


  calculateVideoScreenSize: function () {
    var container = jQuery('#full-screen-video');
    // console.log('Video SIZE:', container.outerWidth());
    var size = getSizeFromVideoProfile();

    // https://math.stackexchange.com/a/180805
    var newHeight = 0;
    console.log('Width:', container.outerWidth());
    if (container.outerWidth() > 520) {
      newHeight = container.outerWidth() * size.height / size.width;
    } else {
      newHeight = container.outerWidth();
    }
    console.log('newHeight:', newHeight);
    container.outerHeight(newHeight);
    return {
      width: container.outerWidth(),
      height: container.outerHeight()
    };
  },

  // get sizes based on the video quality settings
  getSizeFromVideoProfile: function () {
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
  },

  fullscreenInit: function () {
    const resizeVideo = function(firstTime) {
      const size = calculateVideoScreenSize();
      const sliderSize = size.width - 200;
      
      if (!firstTime) {
        // jQuery('.slick-avatars').slick('breakpoint')
      } else {
        jQuery('.remote-users').outerWidth(sliderSize);
      }
      return size;
    }

    const size = resizeVideo(true);
    jQuery(window).smartresize(resizeVideo);

    const sliderSize = size.width - 200;
    const slidesToShow = Math.floor(sliderSize / 110);
    window.slickSettings = {
      dots: false,
      slidesToShow,
      centerMode: false,
      responsive: [{
        breakpoint: 480,
        settings: {slidesToShow: 1}
      }, {
        breakpoint: 600,
        settings: {slidesToShow: 2}
      }, {
        breakpoint: 960,
        settings: {slidesToShow: 3}
      }, {
        breakpoint: 1128,
        settings: {slidesToShow: 4}
      }, {
        breakpoint: 1366,
        settings: {slidesToShow: 5}
      }]
    };
    
    jQuery('#slick-avatars').slick(window.slickSettings);
    // jQuery('.slick-avatars').on('breakpoint', function(event, slick, breakpoint) {
    //   console.log('breakpoint:', breakpoint)
    // })
    initClientAndJoinChannel(window.agoraAppId, window.channelName);
  }
}
