<?php
// $channelSettings is defined on the parent contianer of this file
$recordingSettings = $channelSettings['recording']; ?>
<footer class="agora-footer">
	<div class="buttons-bottom">
		
		<div id="audio-controls" class="col-md-2 text-center btn-group">
	        <button id="mic-btn" type="button" class="btnIcon">
	            <i id="mic-icon" class="fas fa-microphone"></i>
			</button>

          	<button id="mic-dropdown" type="button" class="btnIcon dropdown-toggle dropdown-toggle-split"
          		data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            	<span class="sr-only"><?php _e('Mic Options Dropdown', 'agoraio'); ?></span>
            </button>
            <div id="mic-list" class="dropdown-menu dropdown-menu-right"></div>
            <small class="btn-title"><?php _e('Mic', 'agoraio') ?></small>
        </div>

        <div id="video-controls" class="col-md-2 text-center btn-group">
        	<button id="video-btn"  type="button" class="btnIcon">
        		<i id="video-icon" class="fas fa-video"></i>
        	</button>
        	<button id="cam-dropdown" type="button" class="btnIcon dropdown-toggle dropdown-toggle-split"
        		data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        		<span class="sr-only"><?php _e('Video Options Dropdown', 'agoraio'); ?></span>
        	</button>
        	<div id="camera-list" class="dropdown-menu dropdown-menu-right"></div>
            <small class="btn-title"><?php _e('Video', 'agoraio') ?></small>
        </div>


        <?php if(is_array($recordingSettings) && 
            !empty($recordingSettings['bucket']) &&
            !empty($recordingSettings['accessKey'])) : ?>
        <div class="btn-with-title">
            <button id="cloud-recording-btn" type="button" class="btnIcon start-rec" title="<?php _e('Start Recording', 'agoraio'); ?>">
                <i id="screen-share-icon" class="fas fa-dot-circle"></i>
            </button>
            <small class="btn-title"><?php _e('Record', 'agoraio') ?></small>
        </div>
        <?php endif; ?>

        <div class="btn-with-title">
    		<button id="participants-btn" type="button" class="btnIcon">
    			<i id="participants-icon" class="fas fa-users"></i>
    		</button>
            <small class="btn-title"><?php _e('Participants', 'agoraio') ?></small>
        </div>

        <div class="btn-with-title">
            <button id="rtmp-config-btn" type="button" class="btnIcon" 
                data-toggle="modal" data-target="#addRtmpConfigModal">
                <i id="rtmp-config-icon" class="fas fa-rotate-270 fa-sign-out-alt"></i>
            </button>
            <small class="btn-title"><?php _e('Stream', 'agoraio') ?></small>
        </div>
        <div class="btn-with-title">
    		<button id="add-rtmp-btn"  type="button" class="btnIcon"
    			data-toggle="modal" data-target="#add-external-source-modal">
    			<i id="add-rtmp-icon" class="fas fa-plug"></i>
    		</button>
            <small class="btn-title"><?php _e('Capture', 'agoraio') ?></small>
        </div>
	</div>
</footer>