
(function initRaiseHand($) {

	let raisedHand = false;

    /* When user raises hand */
    jQuery("body").on("click","#raiseHand", function handleRaiseHandRequest() {
        const raiseHand = 'RAISE-HAND-' + window.audienceUserId;
        window.AGORA_RTM_UTILS.sendChatMessage(raiseHand);
        jQuery("#raiseHand").attr("id", "cancelRaiseHand");
        raisedHand = true;
    });

    /* When user cancel raise hand request */
    jQuery("body").on("click","#cancelRaiseHand", function handleCancelRaiseHandRequest() {
        const cancelRaiseHand = 'CANCEL-RAISE-HAND-' + window.audienceUserId;
        window.AGORA_RTM_UTILS.sendChatMessage(cancelRaiseHand);
        jQuery("#cancelRaiseHand").attr("id", "raiseHand");
        raisedHand = true;
    });


})(jQuery);