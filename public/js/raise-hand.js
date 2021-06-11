
(function initRaiseHand($) {

	let raisedHand = false;

    /* When user raises hand */
    jQuery("body").on("click","#raiseHand", function handleRaiseHandRequest() {

        window.rtmChannel.getMembers().then(members => {
            const adminUserRTMId = generateRTMUidfromStreamId(window.adminUser);
            if(members.indexOf(adminUserRTMId) > -1){

                let memberId = adminUserRTMId;
                const msg = {
                    description: undefined,
                    messageType: 'TEXT',
                    rawMessage: undefined,
                    text: 'RAISE-HAND-REQUEST-'+ window.audienceUserId
                }
                try{
                    window.AGORA_RTM_UTILS.sendPeerMessage(msg, memberId);
                } catch(e){

                }
            } else {
                alert("You cannot raise hand right now as Admin User is not available.")
            }
        })


        // const raiseHand = 'RAISE-HAND-' + window.audienceUserId;
        // window.AGORA_RTM_UTILS.sendChatMessage(raiseHand);
        // jQuery("#raiseHand").attr("id", "cancelRaiseHand");
        // raisedHand = true;

    });

    /* When user cancel raise hand request */
    jQuery("body").on("click","#cancelRaiseHand", function handleCancelRaiseHandRequest() {
        const cancelRaiseHand = 'CANCEL-RAISE-HAND--REQUEST-' + window.audienceUserId;
        window.AGORA_RTM_UTILS.sendChatMessage(cancelRaiseHand);
        jQuery("#cancelRaiseHand").attr("id", "raiseHand");
        raisedHand = true;
    });


})(jQuery);