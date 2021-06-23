
(function initRaiseHand($) {

	let raisedHand = false;

    /* When user raises hand */
    jQuery("body").on("click","#raiseHand", function handleRaiseHandRequest() {
        if(window.agoraMode == 'communication'){
            let userName = 'Guest User with id - '+window.localStreams.uid;
            if(typeof window.wp_username!='undefined' && window.wp_username!=""){
                userName = window.wp_username;
            }
            const msg = {
                description: undefined,
                messageType: 'TEXT',
                rawMessage: undefined,
                text: 'RAISE-HAND-' + userName
            }
            try{
                window.AGORA_RTM_UTILS.sendChannelMessage(msg);
                alert("Your Raise hand request has been sent.");
            } catch(e) {

            }
        } else {
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
                        jQuery("#raiseHand").attr("id", "cancelRaiseHand");
                        jQuery("#cancelRaiseHand i").attr('title', 'Cancel Raise Hand Request');
                        sessionStorage.setItem("raisedHandReqUserId", window.userID);
                        raisedHand = true;
                        alert("Your Raise hand request is sent.");
                    } catch(e){

                    }
                } else {
                    alert("You cannot raise hand right now as Admin User is not available.")
                }
            })
        }


        // const raiseHand = 'RAISE-HAND-' + window.audienceUserId;
        // window.AGORA_RTM_UTILS.sendChatMessage(raiseHand);
        // jQuery("#raiseHand").attr("id", "cancelRaiseHand");
        // raisedHand = true;


    });

    /* When user cancel raise hand request */
    jQuery("body").on("click","#cancelRaiseHand", function handleCancelRaiseHandRequest() {

        window.rtmChannel.getMembers().then(members => {
            const adminUserRTMId = generateRTMUidfromStreamId(window.adminUser);
            if(members.indexOf(adminUserRTMId) > -1){

                let memberId = adminUserRTMId;
                const msg = {
                    description: undefined,
                    messageType: 'TEXT',
                    rawMessage: undefined,
                    text: 'CANCEL-RAISE-HAND-REQUEST-'+ window.audienceUserId
                }
                try{
                    window.AGORA_RTM_UTILS.sendPeerMessage(msg, memberId);
                    jQuery("#cancelRaiseHand").attr("id", "raiseHand");
                    jQuery("#raiseHand i").attr('title', 'Raise Hand');
                    raisedHand = false;
                } catch(e){

                }
            } else {
                alert("You cannot cancel raise hand right now as Admin User is not available.")
            }
        })


    });


})(jQuery);