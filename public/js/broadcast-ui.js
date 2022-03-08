window.AGORA_BROADCAST_UI = {
  // UI buttons
  enableUiControls: function (localStream) {

    jQuery("#start-RTMP-broadcast").click(function(){

      const thisBtn = jQuery(this);
      const loaderIcon = jQuery('#rtmp-loading-icon');
      const configIcon = jQuery('#rtmp-config-icon');

      if (thisBtn.hasClass('btn-danger')) {
        thisBtn.toggleClass('btn-danger');
        window.agoraClient.stopLiveStreaming( window.externalBroadcastUrl );
        return false;
      } else if (thisBtn.hasClass('load-rec')) {
        return false;
      } else {
        thisBtn.toggleClass('load-rec');
        configIcon.hide()
        loaderIcon.show()
      }
      
      if (window.defaultConfigRTMP['rtmpServerURL'] && window.defaultConfigRTMP['rtmpServerURL'].length>1) {
        window.AGORA_BROADCAST_CLIENT.startLiveTranscoding();
        // next step: function setupLiveStreamListeners on agora-broadcast-client.js
      }
      // jQuery('#addRtmpConfigModal').modal('toggle');
    });

    jQuery("#add-external-stream").click(function() {
      const formValid = document.getElementById('external-inject-config').checkValidity();
      const errorEl = jQuery('#external-url-error');
      const errorLong = jQuery('#external-url-too-long');
      errorEl.hide();
      errorLong.hide();

      if (!formValid) {
        errorEl.show();
        return;
      }

      const externalUrl = jQuery('#input_external_url').val();
      if (externalUrl.length>255) {
        errorLong.show();
        return;
      }


      const thisBtn = jQuery('#add-rtmp-btn');
      const loaderIcon = jQuery('#add-rtmp-loading-icon');
      const captureIcon = jQuery('#add-rtmp-icon');

      if (thisBtn.hasClass('load-rec')) {
        return false;
      } else {
        thisBtn.toggleClass('load-rec');
        captureIcon.hide()
        loaderIcon.show()
        loaderIcon.attr('style', 'display:inline-block !important');
      }

      // 
      window.AGORA_BROADCAST_CLIENT.addExternalSource();
      jQuery('#add-external-source-modal').modal('toggle');
    });


    jQuery("#stop-rtmp-btn").click(function() {
      window.agoraClient.removeInjectStreamUrl( window.injectedStreamURL );
    })

  },

  toggleCaptureStreamBtn: function(err, status) {
    const thisBtn = jQuery('#add-rtmp-btn');
    const cancelInjectStreamBtn = jQuery('#stop-rtmp-btn')
    const loaderIcon = jQuery('#add-rtmp-loading-icon');
    const captureIcon = jQuery('#add-rtmp-icon');

    const labelStart = thisBtn.parent().find('#label-inject-start');
    const labelStop = thisBtn.parent().find('#label-inject-stop');

    if (status==='started') {
      thisBtn.toggleClass('load-rec');
      thisBtn.hide();
      cancelInjectStreamBtn.show();
      loaderIcon.hide();
      captureIcon.show();

      labelStart.hide();
      labelStop.show();
    } else if (status==='stopped') {
      thisBtn.show();
      cancelInjectStreamBtn.hide();

      labelStop.hide();
      labelStart.show();
    }
  },

  calculateVideoScreenSize: function () {
    var container = jQuery('#full-screen-video');
    var size = window.AGORA_UI_UTILS.getSizeFromVideoProfile();

    // https://math.stackexchange.com/a/180805
    var newHeight = container.outerWidth() * size.height / size.width;
    container.outerHeight(newHeight);
    console.log('Video SIZE:', newHeight);
  },  
}