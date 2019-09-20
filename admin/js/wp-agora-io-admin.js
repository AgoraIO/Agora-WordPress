function updateSettingValue(settingName, newValue, callback) {
	var data = { action: 'save-agora-setting' };
	data[settingName] = newValue;

	var ajaxParams = {
		type: 'POST',
		url: ajaxurl, // from wp admin...
		data
	};
	jQuery.ajax(ajaxParams).then(function(data) {
		callback && callback(null, data);
	}).fail(function(error) {
		console.error('Ajax Error:', error);
		callback && callback(error, null);
	});
}

// jQuery App for each Setting box...
function applicationSettingsForm() {
	var scope = this;
	var settingName = scope.id;
	jQuery(scope).parent().find('a.button').click(function(evt){
		evt.preventDefault();
		var actionButton = jQuery(this);
		actionButton.hide();
		
		var parentActions = actionButton.parent();
		parentActions.addClass('align-right')

		var valueBox = jQuery(scope).find('.value').eq(0);
		var isMasked = valueBox.data('masked');

		var currentValue = valueBox.text().trim();
		var showValue = isMasked ? '' : currentValue;
		// console.log('Value:', currentValue);

		var input = jQuery('<input type="text" style="width:100%" value="'+showValue+'" />');
		jQuery(valueBox).html(input);

		var saveBtn = jQuery('<a href="#" class="button button-primary" id="'+settingName+'-save'+'" style="margin:0 10px">Save</a>');
		var cancelBtn = jQuery('<a href="#" class="button button-secondary" id="'+settingName+'-cancel'+'">Cancel</a>');
		var errorBox = jQuery('<span class="error error-message"></span>');
		parentActions.append(errorBox);
		parentActions.append(saveBtn);
		parentActions.append(cancelBtn);

		var hideSaveButtons = function() {
			saveBtn.remove();
			cancelBtn.remove();
			errorBox.remove();

			parentActions.removeClass('align-right');
			actionButton.show();
		}

		saveBtn.click(function(btnEvt){
			btnEvt.preventDefault();
			var newValue = input.val();
			if (!newValue || newValue.length===0) {
				errorBox.text('Please insert a valid value')
				return false;
			}
			updateSettingValue(settingName, newValue, function(err, res) {
				if (!err) {
					hideSaveButtons();

					if (isMasked) {
						var out = '';
						for(var i=0; i<newValue.length-4;i++) out += '*';
						newValue = out + newValue.substring(newValue.length-4);
					}
					valueBox.html(newValue);
				} else {
					// TODO: Improve error messages!
					errorBox.text('Oops, your data cannot be updated!');
				}
			});
		});

		cancelBtn.click(function(btnEvt){
			btnEvt.preventDefault();
			hideSaveButtons();
			valueBox.html(currentValue);
		});

	});
};

function activateAgoraTabs() {

	// store tabs variables
	var tabs = document.querySelectorAll("ul.nav-tabs > li");

	for (i = 0; i < tabs.length; i++) {
		tabs[i].addEventListener("click", switchTab);
	}

	function switchTab(event) {
		event.preventDefault();
		if (event.target.disabled===true) {
			return false;
		}

		document.querySelector("ul.nav-tabs li.active").classList.remove("active");
		document.querySelector(".tab-pane.active").classList.remove("active");

		var clickedTab = event.currentTarget;
		var anchor = event.target;
		var activePaneID = anchor.getAttribute("href");

		clickedTab.classList.add("active");
		document.querySelector(activePaneID).classList.add("active");
	}
}

(function( $ ) {
	'use strict';

	$( window ).load(function() {
		$('.app-setting').each(applicationSettingsForm);

		if($('#agoraio-new-channel').length>0) {
			activateAgoraTabs();
			$('.agora-color-picker').wpColorPicker();

			$('#type').change(function(){
				var typeChannel = $(this).val();
				var bhr = $('#broadcast-host-row');
				var linkTab2 = $('#link-tab-2');
				var linkTab3 = $('#link-tab-3');
				if (typeChannel==='communication') {
					bhr.hide();
					linkTab2.parent().hide();
					linkTab3.parent().hide();
				} else {
					bhr.show();
					linkTab2.parent().show();
					linkTab3.parent().show();
				}
			});
		}
	});

})( jQuery );
