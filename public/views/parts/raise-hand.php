<div class="<?php if(isset($channelSettings['type']) && $channelSettings['type'] == 'communication'){ echo 'communication-raise-hand-btn'; } else { echo 'fabs'; } ?>">
	<div class="raise-hand-icon" id="raiseHand">
        <i class="far fa-hand-paper" title="Raise Hand"></i>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        showRaiseHandInCommunication();
    });

	window.addEventListener('agora.rtm_init', function loadChatApp() {
        if(window.agoraMode == 'audience' ||  window.agoraMode == 'communication'){
        const headTag = document.head || document.getElementsByTagName("head")[0];

        const chatStyles = document.createElement("link");
        chatStyles.rel = "stylesheet";
        chatStyles.href = `${window.agora_base_url}css/raise-hand.css`;
        headTag.appendChild(chatStyles);

        const arleneLib = document.createElement("script")
        arleneLib.type = "text/javascript";
        arleneLib.src = `${window.agora_base_url}js/raise-hand.js`;
        arleneLib.setAttribute('id', 'wp-agora-raise-hand-js');
        arleneLib.async = true;
        headTag.appendChild(arleneLib);

        if(window.agoraMode == 'audience'){
            /* If user has raised hand and the refresh, skipping 0 - for logged out users, as on refresh, a random uid will be generated. */
            if(sessionStorage.getItem("raisedHandReqUserId")!=0 && sessionStorage.getItem("raisedHandReqUserId") == window.userID){
                jQuery("#raiseHand").attr("id", "cancelRaiseHand");
                jQuery("#cancelRaiseHand i").attr('title', 'Cancel Raise Hand Request');
            }
        }

    } else {
        if(jQuery("script#wp-agora-raise-hand-js").length > 0){
            jQuery("#wp-agora-raise-hand-js").remove();
        }
    }
    });
</script>