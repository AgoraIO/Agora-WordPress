<div class="fabs">
	<a id="chatToggleBtn" class="fab" style="display: none">
		<i id="chat-alert" class="fas fa-bell"></i>
		<i class="fas fa-comment"></i>
	</a>
	<div class="chat">
		<div class="chat_header">
			<?php _e('Chat', 'agoraio'); ?>
			<a id="chat-minimize" class="right"><i class="fas fa-window-minimize"></i></a>
		</div>

		<div id="chat_fullscreen" class="chat_conversion chat_converse"> </div>
		<input type="hidden" id="chat_notify_user_join" value="<?php _e('joined channel', 'agoraio'); ?>">
		<input type="hidden" id="chat_notify_user_leave" value="<?php _e('leave channel', 'agoraio'); ?>">
		<input type="hidden" id="chat_notify_user_welcome" value="<?php _e('Welcome', 'agoraio'); ?>">
		<div class="fab_field user">
			<textarea id="chatSend" name="chat_message" placeholder="Send a message" class="chat_field chat_message"></textarea>
			<a id="fab_send" class="fab"><i class="fas fa-paper-plane"></i></a>
		</div>
		<div class="fab_field non-user">
			<label id="label_chat_name" for="chart_name"><?php _e('Please, enter your name to join chat', 'agoraio'); ?></label>
			<input type="text" name="chat_name" id="chat_name" class="chat_enter_name" />
			<button type="button" id="fab_save_user" class="btnIcon"><?php _e('Join', 'agoraio') ?></button>
			<input type="hidden" id="error_name_length" value="<?php _e('Your name is too short!', 'agoraio'); ?>">
			<input type="hidden" id="error_name_invalid" value="<?php _e("Please, don't use special characters", 'agoraio'); ?>">
		</div>
	</div>
</div>
<script type="text/javascript">
	window.addEventListener('agora.rtm_init', function loadChatApp() {
        const headTag = document.head || document.getElementsByTagName("head")[0];

        const chatStyles = document.createElement("link");
        chatStyles.rel = "stylesheet";
        chatStyles.href = `${window.agora_base_url}css/chat-fab.css`;
        headTag.appendChild(chatStyles);

        const arleneLib = document.createElement("script")
        arleneLib.type = "text/javascript";
        arleneLib.src = `${window.agora_base_url}js/chat.js`;
        arleneLib.async = true;
        headTag.appendChild(arleneLib);
    });
</script>