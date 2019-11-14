var cloudRegions = {
	// North America in China?
  'qiniu': ['East China', 'North China', 'South China', 'North America'],

  'aws': ['US_EAST_1', 'US_EAST_2', 'US_WEST_1', 'US_WEST_2', 'EU_WEST_1', 'EU_WEST_2', 'EU_WEST_3', 'EU_CENTRAL_1', 'AP_SOUTHEAST_1', 'AP_SOUTHEAST_2', 'AP_NORTHEAST_1', 'AP_NORTHEAST_2', 'SA_EAST_1', 'CA_CENTRAL_1', 'AP_SOUTH_1', 'CN_NORTH_1', 'CN_NORTHWEST_1', 'US_GOV_WEST_1'],
  
  'alibaba': ['CN_Hangzhou', 'CN_Shanghai', 'CN_Qingdao', 'CN_Beijing', 'CN_Zhangjiakou', 'CN_Huhehaote', 'CN_Shenzhen', 'CN_Hongkong', 'US_West_1', 'US_East_1', 'AP_Southeast_1', 'AP_Southeast_2', 'AP_Southeast_3', 'AP_Southeast_5', 'AP_Northeast_1', 'AP_South_1', 'EU_Central_1', 'EU_West_1', 'EU_East_1'],
};

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

		if ($('#agoraio-new-channel').length>0) {
			activateAgoraTabs();
			$('.agora-color-picker').wpColorPicker();

			$('#type').change(validateChannelType);

			var channelType = $('#type').val();
			if (channelType && channelType.length>0) {
				$('#type').change();
			}
		}
	});

	function validateChannelType() {
		var typeChannel = $(this).val();
		var bhr = $('#broadcast-host-row');
		var linkTab2 = $('#link-tab-2');
		var linkTab3 = $('#link-tab-3');
		var splashImageURL = $('#splashImageURL').parent().parent();
		var watchButtonText = $('#watchButtonText').parent().parent();
		var watchButtonIcon = $('#watchButtonIcon').parent().parent();
		if (typeChannel==='communication') {
			bhr.hide();
			linkTab2.parent().hide();
			linkTab3.parent().hide();
			splashImageURL.hide();
			watchButtonText.hide();
			watchButtonIcon.hide();
		} else {
			bhr.show();
			linkTab2.parent().show();
			linkTab3.parent().show();
			splashImageURL.show();
			watchButtonText.show();
			watchButtonIcon.show();
		}


		// on communication, also hide Splash screen, and text and icon  button
	}

})( jQuery );
