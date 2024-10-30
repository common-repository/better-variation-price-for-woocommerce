(function($) {

	$(document).ready(function(){

		const displayField = $('#wbvp_display');
		const formatField = $('#wbvp_format');

		function changeFormatPlaceholder() {
			if (displayField.val() == 'none') {
				formatField.attr('disabled', 'disabled');
				formatField.closest('tr').addClass('disabled');
				return;
			}

			formatField.removeAttr('disabled');
			formatField.closest('tr').removeClass('disabled');
			if (formatField.val()) return;

			if (displayField.val() == 'max') formatField.attr('placeholder', lang.max);
			else formatField.attr('placeholder', lang.min);
		}

		displayField.on('change', function(e) {
			changeFormatPlaceholder();
		});

		changeFormatPlaceholder();

	});

})(jQuery);
