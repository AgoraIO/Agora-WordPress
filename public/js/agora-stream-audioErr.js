function addAudioErrorGesture(streamID){
    return '<div id="clickable_'+streamID+'" rel="'+streamID+'" class="resume-userclick" onclick="resumeStream(event, '+streamID+')">Click to unmute</div>';
}

function resumeStream(e, streamID){
    e.stopPropagation();
    let index = Object.keys(window.remoteStreams).find(key => window.remoteStreams[key].stream.getId() === streamID);
    window.remoteStreams[index].stream.resume().then(() => {
        console.log("clickable", streamID);
        handleGhostMode(streamID, 'remote');
        handleMutedVideoBackgroundColor(streamID, 'remote');
        jQuery("body #clickable_"+streamID).remove();
    }).catch(console.warn);
}