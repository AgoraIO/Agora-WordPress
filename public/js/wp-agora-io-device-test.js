jQuery(document).ready(function(){

    if(!window.pre_call_device_test_enabled){
        jQuery('body .agora-footer').css('display', 'flex');
    }

    if(window.pre_call_device_test_enabled){
        jQuery("body #local-video").css('width', '50%');

        let volume_indicator_div = '<span class="test-device-volumeScaleHolder"><span id="test-device-progressBar"><span id="test-device-myVolume"></span></span></span>';

        jQuery("body #screen-users").append("<div id='test-device-section'>Camera <br><div id='test-device-camera-list'><select id='test-device-camera-options'></select></div>Microphone <br /><div id='test-device-mic-list'><select id='test-device-mic-options'></select></div> <div class='test-device-volume-indicator'>"+volume_indicator_div+"</div> <div class='action-buttons'><button onclick='publishLocalStream()'>Click to Join</button></div></div>");
    }

    var i = 0;

    if(window.pre_call_device_test_enabled){
        var volumeData = {avg: 0, val: 0};
        console.log("tunAudoLevelInterval")
        var currStreamInterval = setInterval(function(){
            console.log("setIntervalRun")
            if(typeof window.localStreams.camera.stream!='undefined' && !jQuery.isEmptyObject(window.localStreams.camera.stream)){
                //console.log("setIntervalRunLoclStream")
                console.log("audiLevel", window.localStreams.camera.stream.getAudioLevel().toFixed(3))
                let volume = window.localStreams.camera.stream.getAudioLevel().toFixed(3)*100;

                //jQuery('body #test-device-section .test-device-volume-indicator').find('input').attr('value', volume*100)
                jQuery('body #test-device-myVolume').css('width', volume+'%')

                if(sessionStorage.getItem("deviceTested")=="Yes"){
                    clearInterval(currStreamInterval);
                }
            }
        }, 200);
    }
});


function publishLocalStream (localStream, channelType){

    sessionStorage.setItem("deviceTested", "Yes");

    jQuery("body #local-video").css('width', '100%');

    jQuery('body div#test-device-section').remove();

    jQuery('body .agora-footer').css('display', 'flex');

    // publish local stream
    window.agoraClient.publish(localStream, function (err) {
        AgoraRTC.Logger.error("[ERROR] : publish local stream error: " + err);
    });

    if(channelType == 'communication'){
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