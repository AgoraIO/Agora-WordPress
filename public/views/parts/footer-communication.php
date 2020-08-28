<?php
// $channelSettings is defined on the parent contianer of this file
$recordingSettings = $channelSettings['recording'];
?>
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

        <div class="btn-separator"></div>

        <div class="btn-with-title">
    		<button id="screen-share-btn" type="button" class="btnIcon" title="<?php _e('Screen Share', 'agoraio'); ?>">
              <i id="screen-share-icon" class="fas fa-desktop"></i>
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span>
            </button>
            <small class="btn-title"><?php _e('Share', 'agoraio') ?></small>
        </div>

        <?php /* if(is_array($recordingSettings) && 
            !empty($recordingSettings['bucket']) &&
            !empty($recordingSettings['accessKey'])) : ?>
        <div class="btn-with-title">
            <button id="cloud-recording-btn" type="button" class="btnIcon start-rec" title="<?php _e('Start Recording', 'agoraio'); ?>">
                <i id="screen-share-icon" class="fas fa-dot-circle"></i>
            </button>
            <small class="btn-title"><?php _e('Record', 'agoraio') ?></small>
        </div>
        <?php endif; */ ?>

		<!-- <div class="btn-separator"></div>

        <div class="btn-with-title">
            <button id="chat-btn" class="btnIcon open-chat" title="<?php _e('Open Chat', 'agoraio'); ?>" type="button">
                <i id="chat-icon" class="fas fa-comment-alt"></i>
            </button>
            <small class="btn-title"><?php _e('Chat', 'agoraio') ?></small>
        </div> -->
	</div>
    <div class="error-container">
        <span id="error-msg" class="text-danger"></span>
    </div>
</footer>