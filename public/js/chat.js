
(function initChatApp($) {
	const TOKEN_SEP = 'ππ';	
	const textarea = document.querySelector("#chatSend");
	const chatMsgWindow = $('#chat_fullscreen');
	const chatToggleBtn = $( window.agoraMode==='audience' ? '#chatToggleBtn' : '#chat-btn');
	const chatMinBtn = $('#chat-minimize');
	const chatNameInput = document.querySelector('#chat_name');
	const chatAlert = document.querySelector('#chat-alert');
	const chatRoot = $('.chat');
	let lastUserChat = '';


	if (window.userID > 0) {
		document.querySelector('.fab_field.non-user').style.display='none';
		const dataJoin = 'CHAT-JOIN' + TOKEN_SEP + window.userID + TOKEN_SEP + window.wp_username;
		window.AGORA_RTM_UTILS.sendChatMessage(dataJoin);
		chatMsgWindow.css('display', 'block');

		const localData = window.userID + TOKEN_SEP + window.wp_username;
		showUserNotify(localData, 'welcome')

		textarea.addEventListener('focus', scrollOnMobileView);
	} else {
		document.querySelector('.fab_field.user').style.display='none';
		chatMsgWindow.css('display', 'none');
		initNonUserActions();
	}

	// hide chat UI to start
	$('#fab_send').click(sendMessage);

	$('#chat_converse').css('display', 'none');
	$('#chat_body').css('display', 'none');
	$('#chat_form').css('display', 'none');

	//Toggle chat and links
	function toggleChatWindow() {

		if (window.AGORA_RTM_UTILS.connectionState!==AgoraRTM.ConnectionState.CONNECTED) {
			return false;
		}

		// chatToggleBtn.toggleClass('is-float');
		$('.prime').toggleClass('zmdi-comment-outline');
		$('.prime').toggleClass('zmdi-close');
		$('.prime').toggleClass('is-active');
		$('.prime').toggleClass('is-visible');
		$('.fab').toggleClass('is-visible');
		chatRoot.toggleClass('is-visible');

		if (chatRoot.hasClass('is-visible')) {
			scrollToBottm();
			if (window.wp_username) {
				textarea.focus()
			} else {
				chatNameInput.focus()
			}
		}

		// available only on broadcast and communication. not on audience view
		if (chatAlert) {
			chatAlert.style.opacity = 0;
		}
	}

	chatMinBtn.click(toggleChatWindow);
	chatToggleBtn.click(toggleChatWindow);

	window.toggleChatWindow = toggleChatWindow;


	function initNonUserActions() {
		document.querySelector('#fab_save_user').addEventListener('click', validateGuestUser);
		chatNameInput.addEventListener('keyup', validateKeyGuestUser);

		function validateKeyGuestUser(e) {
			if ( e.key==='Enter' ) { return validateGuestUser() }
		}

		function validateGuestUser() {
			const name = chatNameInput.value.trim();
			const label = document.getElementById('label_chat_name');
			if (name.length===0) {
				label.classList.add('error');
				return;
			}
			if (name.length<3) {
				label.classList.add('error');
				label.innerText = document.getElementById('error_name_length').value;
				return;
			}

			if (name.search(/\$|@|#|!|\?|<|>|\(|\)|;|:|\"|\'|\\|\{|\}|\[|\]|%|=|\+|\?|\/|\./) >= 0) {
				label.classList.add('error');
				label.innerText = document.getElementById('error_name_invalid').value;
				return;	
			}

			label.classList.remove('error')
			label.innerText = ''
			window.wp_username = name
			
			// Report to other about me!
			const data = 'CHAT-JOIN' + TOKEN_SEP + window.userID + TOKEN_SEP + window.wp_username;
			window.AGORA_RTM_UTILS.sendChatMessage(data);

			const localData = window.userID + TOKEN_SEP + window.wp_username;
			showUserNotify(localData, 'welcome')

			// Change the UI
			document.querySelector('.fab_field.non-user').style.display='none'
			document.querySelector('.fab_field.user').style.display='block'
			chatMsgWindow.css('display', 'block')
			textarea.focus()
		}

		chatNameInput.addEventListener('focus', scrollOnMobileView)
	}

	function scrollOnMobileView() {
		if (window.innerWidth < 561) {
			this.scrollIntoView({behavior: "smooth", block: "center"});
		}
	}


	// resizable text area
	function calcHeight(value) {
	  let numberOfLineBreaks = (value.match(/\n/g) || []).length;
	  // min-height + lines x line-height + padding + border
	  let newHeight = 25;
	  if(numberOfLineBreaks > 0) {
	    newHeight = 25 + numberOfLineBreaks * 25 + 12 + 2;
	  }
	  
	  return newHeight;
	}

	// resize on key-up event
	textarea && textarea.addEventListener("keydown", resizeTextArea);

	function resizeTextArea(e) {
		if (e && e.key === 'Enter' && !e.shiftKey) {
			e.preventDefault();
			sendMessage();
	    	// textarea.value = textarea.value.substring(0, -1)
	    	return false;
	    }
	    textarea.style.height = calcHeight(textarea.value) + "px";
	}

	// chat msg UI
	function scrollToBottm() {
	  chatMsgWindow.animate({
	    scrollTop: chatMsgWindow[0].scrollHeight
	  }, 500);
	}

	function addLocalMsg(msg) {
		const msgLine = $('<div/>', {class: 'chat-msg-line local'});

		const user = window.wp_username;
		if (user !== lastUserChat) {
			lastUserChat = user;
			const msgTime = getMessageTime();
			const labelTxt = `${user} <time>${msgTime}</time>`;
			msgLine.append($('<label>', {class:'chat_username'}).append(labelTxt))
		}

		msgLine.append(
			$('<span/>', {'class': 'chat_msg_item chat_msg_item_local_user'}).append(msg)
		);
		chatMsgWindow.append(msgLine);
		// scroll to bottom
		scrollToBottm();
	}

	function getMessageTime() {
		const now = new Date();
		let hour = now.getHours();
		let suffix = 'am';
		if (hour===0) {
			hour = 12;
		}
		if (hour>12) {
			hour-=12;
			suffix = 'pm';
		}
		return `${hour}:${now.getMinutes()} ${suffix}`;
	}

	function showUserNotify(msgData, type) {
		const blocksMsg = msgData.split(TOKEN_SEP);
		const uid  = blocksMsg[0];
		const user = (blocksMsg[1] == 'undefined') ? 'anonymous user' : blocksMsg[1];

		const joinMsgEl = document.querySelector('#chat_notify_user_'+type);

		let joinMsg =  user + ' ' + joinMsgEl.value;
		if (type==='welcome') {
			// joinMsg = joinMsgEl.value + ' ' + user;
			joinMsg = user + ' joined the channel';
		} else if (type==='leave') {
			joinMsg = user + ' left the channel';
		}

		chatMsgWindow.append(
			$('<div/>', {class: 'chat-msg-line chat-notify uid' + uid})
			.append(`-- ${joinMsg} --`)
		);
	}

	function addRemoteMsg(uidRTM, data) {
		const blocksMsg = data.split(TOKEN_SEP);

		const uid  = blocksMsg[0];
		const user = blocksMsg[1];
		const msg  = blocksMsg[2];

		const msgLine = $('<div/>', {class: 'chat-msg-line remote uid' + uid});
			
		if (user !== lastUserChat) {
			lastUserChat = user;
			const msgTime = getMessageTime();
			const labelTxt = `${user} <time>${msgTime}</time>`;
			msgLine.append($('<label>', {class:'chat_username'}).append(labelTxt))
		}

		const avatarElement = $('<div/>', {'class': 'chat_avatar'});
		loadUserAvatar(uid, avatarElement[0]);
		msgLine.append(
			$('<div/>', {'class': 'chat_msg_item chat_msg_item_remote_user'})
			.append(avatarElement)
			.append( $('<span/>').append(msg) )
		)

		chatMsgWindow.append(msgLine);
		
		if (chatRoot.hasClass('is-visible')){
			scrollToBottm();
		} else if (chatAlert) {
			chatAlert.style.opacity = 1;
		}
	}

	function sendMessage() {
	  const msg = textarea.value.replace(/\n/g, '<br/>');
	  if (msg.length>0) {
	  	const uid = window.userID;
	  	const data = `CHATππ${uid}ππ${window.wp_username}ππ${msg}`;
		window.AGORA_RTM_UTILS.sendChatMessage(data, function() {
			addLocalMsg(msg);
			textarea.value = ""; // after the message is sent clear the text area.
			resizeTextArea();
		});
	  }
	}

	const loadedAvatars = {};
	function loadUserAvatar(uid, div) {
		function setSourceAvatar(url) {
			div.style.backgroundImage = `url(${url})`;
			div.style.backgroundSize = 'cover';
			div.style.backgroundRepeat = 'no-repeat';
		}

		if (loadedAvatars[uid]) {
			setSourceAvatar(loadedAvatars[uid])
			return;
		}
		const appendAvatar = function(avatarData) {
			if (avatarData && avatarData.avatar) {
				const url = avatarData.avatar.url
				setSourceAvatar(url);
				loadedAvatars[uid] = url;
			}
		};

		window.AGORA_UTILS.agora_getUserAvatar(uid, appendAvatar);
	}

	window.AGORA_RTM_UTILS.addLocalMsg = addLocalMsg;
	window.AGORA_RTM_UTILS.addRemoteMsg = addRemoteMsg;

	window.addEventListener('agora.rtmMessageFromChannel', function(evt){
		// console.log('rtmMessageFromChannel', evt.detail);
		if (evt.detail && evt.detail.text) {
			if (evt.detail.text.indexOf(`CHAT${TOKEN_SEP}`)===0) {
				const msgData = evt.detail.text.substring(6);
				window.AGORA_RTM_UTILS.addRemoteMsg(evt.detail.senderId, msgData)
			} else if (evt.detail.text.indexOf(`CHAT-JOIN${TOKEN_SEP}`)===0) {
				const msgData = evt.detail.text.substring(11);
				showUserNotify(msgData, 'join');
			} else if (evt.detail.text.indexOf(`CHAT-LEAVE${TOKEN_SEP}`)===0) {
				const msgData = evt.detail.text.substring(12);
				showUserNotify(msgData, 'leave');
			}
		}
	});

	// Event listener when user leave the current channel, on agora-rtm.js
	window.addEventListener('agora.leavingChannel', function leaveChat() {
		if (chatRoot.hasClass('is-visible')) {
			window.toggleChatWindow();
		}
		chatMsgWindow.empty();
		const uid = window.userID;
	  	const data = 'CHAT-LEAVE' + TOKEN_SEP + uid + TOKEN_SEP + window.wp_username;
		window.AGORA_RTM_UTILS.sendChatMessage(data);
	})


})(jQuery);