<div class="fabs">
	<div class="raise-hand-icon" id="raiseHand">
        <i class="far fa-hand-paper"></i>
    </div>
</div>

<script type="text/javascript">
	window.addEventListener('agora.rtm_init', function loadChatApp() {
        const headTag = document.head || document.getElementsByTagName("head")[0];

        const chatStyles = document.createElement("link");
        chatStyles.rel = "stylesheet";
        chatStyles.href = `${window.agora_base_url}css/raise-hand.css`;
        headTag.appendChild(chatStyles);

        const arleneLib = document.createElement("script")
        arleneLib.type = "text/javascript";
        arleneLib.src = `${window.agora_base_url}js/raise-hand.js`;
        arleneLib.async = true;
        headTag.appendChild(arleneLib);
    });
</script>