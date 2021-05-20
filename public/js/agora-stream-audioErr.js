function addAudioErrorGesture(streamID){
    return '<div id="clickable_'+streamID+'" rel="'+streamID+'" class="resume-userclick" onclick="resumeStream(event, '+streamID+')">Click to unmute</div>';
}

function resumeStream(e, streamID){
    e.stopPropagation();
    let index = window.allStreams.findIndex(x => x.getId() ===streamID);
    window.allStreams[index].resume().then(() => {
        console.log("clickable");
        jQuery("body #clickable_"+streamID).remove();
    }).catch(console.warn);
}