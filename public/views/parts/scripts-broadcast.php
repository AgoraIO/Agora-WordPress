<script type="text/javascript">
	window.externalBroadcastUrl = '';

    // default config for rtmp
    window.defaultConfigRTMP = {
      rtmpServerURL: "<?php echo isset($videoSettings['external-rtmpServerURL']) ? $videoSettings['external-rtmpServerURL'] : '' ?>",
        streamKey: "<?php echo isset($videoSettings['external-streamKey']) ? $videoSettings['external-streamKey'] : "" ?>",
      width: <?php echo $videoSettings['external-width'] ?>,
      height: <?php echo $videoSettings['external-height'] ?>,
      videoBitrate: <?php echo $videoSettings['external-videoBitrate'] ?>,
      videoFramerate: <?php echo $videoSettings['external-videoFramerate'] ?>,
      lowLatency: <?php echo $videoSettings['external-lowLatency'] ?>,
      audioSampleRate: <?php echo $videoSettings['external-audioSampleRate'] ?>,
      audioBitrate: <?php echo $videoSettings['external-audioBitrate'] ?>,
      audioChannels: <?php echo $videoSettings['external-audioChannels'] ?>,
      videoGop: <?php echo $videoSettings['external-videoGop'] ?>,
      videoCodecProfile: <?php echo $videoSettings['external-videoCodecProfile'] ?>,
      userCount: 1,
      userConfigExtraInfo: {},
      backgroundColor: parseInt('<?php echo str_replace('#', '', $videoSettings['external-backgroundColor']) ?>', 16),
      transcodingUsers: [{
        uid: window.userID,
        alpha: 1,
        width: <?php echo $videoSettings['external-width'] ?>,
        height: <?php echo $videoSettings['external-height'] ?>,
        x: 0,
        y: 0,
        zOrder: 0
      }],
    };

    window.injectStreamConfig = {
      width: <?php echo $videoSettings['inject-width'] ?>,
      height: <?php echo $videoSettings['inject-height'] ?>,
      videoBitrate: <?php echo $videoSettings['inject-videoBitrate'] ?>,
      videoFramerate: <?php echo $videoSettings['inject-videoFramerate'] ?>,
      audioSampleRate: <?php echo $videoSettings['inject-audioSampleRate'] ?>,
      audioBitrate: <?php echo $videoSettings['inject-audioBitrate'] ?>,
      audioChannels: <?php echo $videoSettings['inject-audioChannels'] ?>,
      videoGop: <?php echo $videoSettings['inject-videoGop'] ?>,
    };
</script>