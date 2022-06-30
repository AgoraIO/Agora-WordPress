/**
 * Agora Broadcast Client 
 */
 // create client instance
window.agoraClient = AgoraRTC.createClient({mode: 'live', codec: 'vp8'}); // h264 better detail at a higher motion


const AGORA_RADIX_DECIMAL = 10;
const AGORA_RADIX_HEX = 16;
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

window.AGORA_BROADCAST_CLIENT = {
  startLiveTranscoding: startLiveTranscoding,
  addExternalSource: addExternalSource,
};

function startLiveTranscoding() {
  AgoraRTC.Logger.info("Start live transcoding..."); 
  const rtmpURL = window.defaultConfigRTMP.rtmpServerURL;
  const rtmpKey = window.defaultConfigRTMP.streamKey;

  if (!rtmpURL || rtmpURL.indexOf('://')<0) {
    alert('Please, configure a valid RTMP URL on your "External Networks" settings')
    return false;
  }

  // set live transcoding config
  window.defaultConfigRTMP.transcodingUsers[0].uid = window.localStreams.uid;
  window.agoraClient.setLiveTranscoding(window.defaultConfigRTMP);

  if (rtmpURL.length>0) {
    const sep = rtmpURL.lastIndexOf('/')===rtmpURL.length-1 ? '' : '/';
    window.externalBroadcastUrl = rtmpURL + sep + rtmpKey;
    console.log(window.externalBroadcastUrl);

    window.agoraClient.startLiveStreaming(window.externalBroadcastUrl, true)
    // addExternalTransmitionMiniView(window.externalBroadcastUrl)
  }
}

// window.AGORA_BROADCAST_CLIENT.startLiveTranscoding = startLiveTranscoding;

function addExternalSource() {
  const externalUrl = jQuery('#input_external_url').val();
  
  // set live transcoding config
  window.injectedStreamURL = externalUrl;
  window.agoraClient.addInjectStreamUrl(externalUrl, window.injectStreamConfig)
}

// RTMP Connection (UI Component)
function addExternalTransmitionMiniView(rtmpURL) {
  var container = jQuery('#rtmp-controlers');
  // append the remote stream template to #remote-streams
  container.append(
    jQuery('<div/>', {'id': 'rtmp-container',  'class': 'container row justify-content-end mb-2'}).append(
      jQuery('<div/>', {'class': 'pulse-container'}).append(
          jQuery('<button/>', {'id': 'rtmp-toggle', 'class': 'btn btn-lg col-flex pulse-button pulse-anim mt-2'})
      ),
      jQuery('<input/>', {'id': 'rtmp-url', 'val': rtmpURL, 'class': 'form-control col-flex" value="rtmps://live.facebook.com', 'type': 'text', 'disabled': true}),
      jQuery('<button/>', {'id': 'removeRtmpUrl', 'class': 'btn btn-lg col-flex close-btn'}).append(
        jQuery('<i/>', {'class': 'fas fa-xs fa-trash'})
      )
    )
  );
  
  jQuery('#rtmp-toggle').click(function() {
    if (jQuery(this).hasClass('pulse-anim')) {
      window.agoraClient.stopLiveStreaming(window.externalBroadcastUrl)
    } else {
      window.agoraClient.startLiveStreaming(externalBroadcastUrl, true)
    }
    jQuery(this).toggleClass('pulse-anim');
    jQuery(this).blur();
  });

  jQuery('#removeRtmpUrl').click(function() { 
    window.agoraClient.stopLiveStreaming(window.externalBroadcastUrl);
    window.externalBroadcastUrl = '';
    jQuery('#rtmp-container').remove();
  });
}


window.addEventListener('agora.rtm_init', function() {
  setupLiveStreamListeners();
  setupInjectStreamsListeners();
});

function setupLiveStreamListeners() {
  function toggleStreamButton(err, status) {
    const thisBtn    = jQuery("#start-RTMP-broadcast");
    const loaderIcon = thisBtn.find('#rtmp-loading-icon');
    const configIcon = thisBtn.find('#rtmp-config-icon');
    const labelStart = thisBtn.parent().find('#label-stream-start');
    const labelStop = thisBtn.parent().find('#label-stream-stop');

    if (thisBtn.hasClass('load-rec')) {
      thisBtn.toggleClass('load-rec');
      configIcon.show()
      loaderIcon.hide()
    }

    if (!err && status==='started') {
      thisBtn.addClass('btn-danger');
      labelStart.hide();
      labelStop.show();

    } else if (!err && status==='stopped') {
      thisBtn.removeClass('btn-danger');
      labelStart.show();
      labelStop.hide();
    }

    if (err && err.reason) {
      window.AGORA_UTILS.showErrorMessage(err.reason)
    }
  }

  window.agoraClient.on('liveStreamingStarted', function (evt) {
    console.log("Live streaming started", evt);
    toggleStreamButton(null, 'started')
  }); 

  window.agoraClient.on('liveStreamingFailed', function (evt) {
    console.log("Live streaming failed", evt);
    toggleStreamButton(evt)
  }); 

  window.agoraClient.on('liveStreamingStopped', function (evt) {
    console.log("Live streaming stopped", evt);
    toggleStreamButton(null, 'stopped')
  });

  window.agoraClient.on('liveTranscodingUpdated', function (evt) {
    console.log("Live streaming updated", evt);
  });
}

function setupInjectStreamsListeners() {
  window.agoraClient.on('streamInjectedStatus', function (evt) {
    console.log("Live streaming Injected Status:", evt);

    const thisBtn = jQuery('#add-rtmp-btn');
    const loaderIcon = thisBtn.find('#add-rtmp-loading-icon');
    const captureIcon = thisBtn.find('#add-rtmp-icon');

    if (evt.reason && evt.reason.indexOf('fail')>=0) {
      window.AGORA_UTILS.showErrorMessage(evt.reason);
      loaderIcon.hide();
      captureIcon.show();
    }
  });

  window.agoraClient.on('exception', function (ex) {
    console.log("Agora Exception:", ex);
  });
}
//window.AGORA_UTILS.setupAgoraListeners();