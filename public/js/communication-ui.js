
window.AGORA_COMMUNICATION_UI = {
// UI buttons
  enableUiControls: function (localStream) {

    jQuery("#mic-btn").prop("disabled", false);
    jQuery("#video-btn").prop("disabled", false);
    jQuery("#screen-share-btn").prop("disabled", false);
    jQuery("#exit-btn").prop("disabled", false);

    jQuery("#mic-btn").click(function(){
      window.AGORA_COMMUNICATION_UI.toggleMic(localStream);
    });

    jQuery("#video-btn").click(function(){
      window.AGORA_COMMUNICATION_UI.toggleVideo(localStream);
    });

    jQuery("#screen-share-btn").click(function() {
      window.AGORA_SCREENSHARE_UTILS.toggleScreenShareBtn(); // set screen share button icon
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
        window.AGORA_SCREENSHARE_UTILS.stopScreenShare(toggleLoader);
      } else {
        window.AGORA_SCREENSHARE_UTILS.initScreenShare(toggleLoader);
      }
    });

    jQuery("#exit-btn").click(function(){
      console.log("so sad to see you leave the channel");
      window.AGORA_COMMUNICATION_CLIENT.agoraLeaveChannel(); 
    });

    jQuery('#rejoin-btn').click(window.AGORA_COMMUNICATION_UI.rejoinChannel);

    // keyboard listeners 
    jQuery(document).keypress(function(e) {
      switch (e.key) {
        case "m":
          console.log("squick toggle the mic");
          window.AGORA_COMMUNICATION_UI.toggleMic(localStream);
          break;
        case "v":
          console.log("quick toggle the video");
          window.AGORA_COMMUNICATION_UI.toggleVideo(localStream);
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
          window.AGORA_COMMUNICATION_CLIENT.agoraLeaveChannel(); 
          break;   
        default:  // do nothing
      }

      // (for testing) 
      if(e.key === "r") { 
        // window.history.back(); // quick reset
      }
    });
  },

  toggleMic: function (localStream) {
    console.log(localStream)
    window.AGORA_UTILS.toggleBtn(jQuery("#mic-btn")); // toggle button colors
    jQuery("#mic-icon").toggleClass('fa-microphone', localStream.userMuteAudio).toggleClass('fa-microphone-slash', !localStream.userMuteAudio); // toggle the mic icon

    if (!localStream.userMuteAudio) {
      localStream.muteAudio(); // disable the local video
      window.AGORA_UTILS.toggleVisibility("#mute-overlay", true); // show the muted mic icon
    } else {
      localStream.unmuteAudio(); // enable the local mic
      window.AGORA_UTILS.toggleVisibility("#mute-overlay", false); // hide the muted mic icon
    }
  },

  toggleVideo: function (localStream) {
    window.AGORA_UTILS.toggleBtn(jQuery("#video-btn")); // toggle button colors
    jQuery("#video-icon").toggleClass('fa-video', localStream.userMuteVideo).toggleClass('fa-video-slash', !localStream.userMuteVideo); // toggle the video icon

    if (!localStream.userMuteVideo) {
      localStream.muteVideo(); // disable the local video
      window.AGORA_UTILS.toggleVisibility("#no-local-video", true); // show the user icon when video is disabled
    } else {
      localStream.unmuteVideo(); // enable the local video
      window.AGORA_UTILS.toggleVisibility("#no-local-video", false); // hide the user icon when video is enabled
      window.AGORA_COMMUNICATION_UI.logCameraDevices();
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
      // joinChannel(window.channelName);
      if (jQuery("#mic-icon").hasClass('fa-microphone-slash')) {
        jQuery("#mic-icon").toggleClass('fa-microphone').toggleClass('fa-microphone-slash');
        window.AGORA_UTILS.toggleVisibility("#mute-overlay", false); // hide the muted mic icon
      }
      
      if (jQuery("#video-icon").hasClass('fa-video-slash')) {
        jQuery("#video-icon").toggleClass('fa-video').toggleClass('fa-video-slash'); // toggle the video icon
        window.AGORA_UTILS.toggleVisibility("#no-local-video", false); // hide the user icon when video is enabled
      } 
      window.AGORA_COMMUNICATION_CLIENT.agoraJoinChannel(window.channelName);
    }
  },


  calculateVideoScreenSize: function () {
    var container = jQuery('#full-screen-video');
    // console.log('Video SIZE:', container.outerWidth());
    var size = window.AGORA_COMMUNICATION_UI.getSizeFromVideoProfile();

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
      const size = window.AGORA_COMMUNICATION_UI.calculateVideoScreenSize();
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
    window.AGORA_COMMUNICATION_CLIENT.initClientAndJoinChannel(window.agoraAppId, window.channelName);
  }
}
