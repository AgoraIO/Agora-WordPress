function updateSettingValue(settingName, newValue, callback) {
	var data = { action: 'save-agora-setting' };
	data[settingName] = newValue;
	
	var ajaxParams = {
		type: 'POST',
		url: ajaxurl, // from wp admin...
		data
	};
	jQuery.ajax(ajaxParams).then(function(data) {
		callback && callback();
	}).fail(function(error) {
		console.error('Ajax Error:', error);
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
		var currentValue = valueBox.text().trim();
		// console.log(currentValue);
		var input = jQuery('<input type="text" style="width:100%" value="'+currentValue+'" />');
		jQuery(valueBox).html(input);

		var saveBtn = jQuery('<a href="#" class="button button-primary" id="'+settingName+'-save'+'" style="margin-right:10px">Save</a>');
		var cancelBtn = jQuery('<a href="#" class="button button-secondary" id="'+settingName+'-cancel'+'">Cancel</a>');
		parentActions.append(saveBtn);
		parentActions.append(cancelBtn);

		var hideSaveButtons = function() {
			saveBtn.remove();
			cancelBtn.remove();

			actionButton.show();
			parentActions.removeClass('align-right');

			valueBox.html(input.val());
		}

		saveBtn.click(function(btnEvt){
			btnEvt.preventDefault();
			var newValue = input.val();
			updateSettingValue(settingName, newValue, hideSaveButtons);
		});

		cancelBtn.click(function(btnEvt){
			btnEvt.preventDefault();
			hideSaveButtons();
		});

	});
};

(function( $ ) {
	'use strict';

	$( window ).load(function() {
		$('.app-setting').each(applicationSettingsForm);
	});

})( jQuery );
