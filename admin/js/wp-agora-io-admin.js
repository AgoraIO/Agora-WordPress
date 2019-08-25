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

		var currentValue = !isMasked ? valueBox.text().trim() : '';
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
		}

		saveBtn.click(function(btnEvt){
			btnEvt.preventDefault();
			var newValue = input.val();
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
					// TODO: Show error
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

(function( $ ) {
	'use strict';

	$( window ).load(function() {
		$('.app-setting').each(applicationSettingsForm);
	});

})( jQuery );
