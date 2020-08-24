

window.AGORA_RTM_UTILS = {
	setupRTM: function(agoraAppId, channelName) {
		window.rtmClient = AgoraRTM.createInstance(agoraAppId);
  		window.rtmChannel = rtmClient.createChannel(channelName);

		window.rtmClient.on('ConnectionStateChange', (newState, reason) => {
			alert(newState)
			AgoraRTC.Logger.info('on connection state changed to ' + newState + ' reason: ' + reason);
		});

		// event listener for receiving a peer-to-peer message.
		window.rtmClient.on('MessageFromPeer', ({ text }, peerId) => { 
			// text: text of the received message; peerId: User ID of the sender.
			console.log('AgoraRTM msg from user ' + peerId + ' received: \n' + text);
			processRtmRequest(text);
		});

		// event listener for receiving a channel message
		window.rtmChannel.on('ChannelMessage', ({ text }, senderId) => {
			// text: text of the received channel message; senderId: user ID of the sender.
			console.log('AgoraRTM msg from user ' + senderId + ' received: \n' + text);
			processRtmRequest(text);
		});

		window.rtmChannel.on('MemberJoined', memberId => {
			console.log('arrived member', memberId)
			// if i'm sharing my screen, update the new users layouts
		    if (window.localStreams.screen.id && window.localStreams.screen.id>1) {
		      const msg = { 
		        description: undefined,
		        messageType: 'TEXT',
		        rawMessage: undefined,
		        text: window.localStreams.screen.id + ': start screen share'
		      }
		      window.rtmClient.sendMessageToPeer(msg, memberId).then(() => {
		        console.log('Reported ScreenShare to RTM client.')
		      }).catch(error => {
		        console.error('RTM Error', error)
		      });
		    }
		})
	},

	joinRTMChannel: function(uid) {
		const token = window.AGORA_TOKEN_UTILS.agoraGenerateToken();
		const numberUID = uid < 1000 ? uid + 1000 : uid;
		const finalUID = 'x' + String(numberUID);

		const successToken = (err, token) => {
			if (err) {
				console.error('Token error', err);
				alert('Your Token Server is not Configured, this page will reload!');
				window.location.reload(true);
				return;
			}

			const loginData = { token, uid: finalUID };
			window.rtmClient.login(loginData).then(() => {
				console.log('Agora RTM client login success');

				window.rtmChannel.join().catch(err => {
					console.error('RTM Join Error', err)
					window.rtmChannel = false;
				})
			}).catch(err => {
				console.error('Agora RTM login failure!', err);
			});
		};

		window.AGORA_SCREENSHARE_UTILS.agora_generateAjaxTokenRTM(successToken, finalUID);
	}
}

function processRtmRequest(value) {
	const msgParts = value.split(':');
	if (value.indexOf('start screen share')>0) {
		const uid = msgParts[0];
		if (window.screenshareClients[uid]) {
			console.log(uid, 'Already added as screenshare')
			return;
		}
		console.log('Adding remote screen share:', uid)

		window.screenshareClients[uid] = 1;

	    // in case the screen stream is already shown in the layout, it's needed to udpate the layout:
	    if (window.remoteStreams[uid]) {
	      // first remove the current screen stream from the normal users layout
	      const screenStream = window.remoteStreams[uid];
	      deleteRemoteStream(uid);
	      screenStream.stop();
	      
	      const usersCount = Object.keys(window.remoteStreams).length + 1
	      window.AGORA_UTILS.updateUsersCounter(usersCount);

	      window.AGORA_SCREENSHARE_UTILS.addRemoteScreenshare(screenStream);
	      window.screenshareClients[uid] = screenStream;
	  	}
	}
}