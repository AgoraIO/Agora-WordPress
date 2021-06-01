var currStreamInterval = '';

jQuery(document).ready(function(){

    if(!window.pre_call_device_test_enabled){
        jQuery('body .agora-footer').css('display', 'flex');
    }

    if(window.pre_call_device_test_enabled){
        jQuery("body #local-video").css('width', '50%');
        jQuery("body #full-screen-video").css('width', '50%');

        let volume_indicator_div = '<span class="test-device-volumeScaleHolder"><span id="test-device-progressBar"><span id="test-device-myVolume"></span></span></span>';
        let camera_devices_div = "Camera <br><div id='test-device-camera-list'><select id='test-device-camera-options'></select></div>";
        let testMicButton = "<button onClick='startMicrophoneTesting()'>Start</button>";
        let mic_devices_div = "Microphone <br /><div id='test-device-mic-list'><select id='test-device-mic-options'></select><div id='test_microphone_div'>"+testMicButton+"</div></div>";
        let action_button_div = "<div class='action-buttons'><button onclick='publishLocalStream(window.localStreams.camera.stream)'>Click to Join</button></div>";
        jQuery("body #screen-users").append("<div id='test-device-section'> "+camera_devices_div+mic_devices_div+" <div class='test-device-volume-indicator'>"+volume_indicator_div+"</div> "+action_button_div+" </div>");
    }
});

/* Show Audio Level on device test - Microphone */
function startMicrophoneTesting(){
    jQuery("#test_microphone_div").html("<button onClick='stopMicrophoneTesting()'>Stop</button>");
    jQuery(".test-device-volume-indicator").show();

    currStreamInterval = setInterval(function(){
        if(typeof window.localStreams.camera.stream!='undefined' && !jQuery.isEmptyObject(window.localStreams.camera.stream)){
            
            let volume = window.localStreams.camera.stream.getAudioLevel().toFixed(3)*100;
            jQuery('body #test-device-myVolume').css('width', volume+'%')

            /* Clear Interval when stream is published */
            if(sessionStorage.getItem("deviceTested")=="Yes"){
                clearInterval(currStreamInterval);
            }
        }
    }, 200);
}

/* Stop or Remove Audio Level on device test - Microphone */
function stopMicrophoneTesting(){
    jQuery("#test_microphone_div").html("<button onClick='startMicrophoneTesting()'>Start</button>");
    jQuery(".test-device-volume-indicator").hide();
    if(currStreamInterval!=''){
        clearInterval(currStreamInterval);
    }
}

function publishLocalStream (localStream){

    /* set Value in session storage to handle state on refresh */
    sessionStorage.setItem("deviceTested", "Yes");

    jQuery("body #local-video").css('width', '100%');
    jQuery("body #full-screen-video").css('width', '100%');

    jQuery('body div#test-device-section').remove();

    jQuery('body .agora-footer').css('display', 'flex');

    // publish local stream
    window.agoraClient.publish(localStream, function (err) {
        AgoraRTC.Logger.error("[ERROR] : publish local stream error: " + err);
    });

    if(window.agoraMode == 'communication'){
        window.AGORA_COMMUNICATION_UI.enableUiControls(localStream); // move after testing
    }

    window.localStreams.camera.stream = localStream; // keep track of the camera stream for later

    /* Mute Audios and Videos Based on Mute All Users Settings */
    if(window.mute_all_users_audio_video){
        if(localStream.getVideoTrack() && localStream.getVideoTrack().enabled){
            jQuery("#video-btn").trigger('click');
        }
        if(localStream.getAudioTrack() && localStream.getAudioTrack().enabled){
            jQuery("#mic-btn").trigger('click');
        }
    }
}