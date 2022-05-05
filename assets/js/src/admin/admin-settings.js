function showEmbedOptions(field, multiple) {
	if (multiple === '0') {
		$(field).parents('form').find('h2:nth-of-type(2)').hide();
		$(field).parents('form').find('h2:nth-of-type(3)').show();
		$(field).parents('form').find('table:nth-of-type(2)').hide();
		$(field).parents('form').find('table:nth-of-type(3)').show();
	} else if (multiple === '1') {
		$(field).parents('form').find('h2:nth-of-type(2)').show();
		$(field).parents('form').find('h2:nth-of-type(3)').hide();
		$(field).parents('form').find('table:nth-of-type(2)').show();
		$(field).parents('form').find('table:nth-of-type(3)').hide();
	}
}

// handle the checkbox that switches between single/multiple ads
$(document).on(
	'click',
	'input[name="arcads_dfp_acm_provider_multiple_embeds[]"]',
	function () {
		const multiple = $(this).val();
		showEmbedOptions($(this), multiple);
	}
);

// on load, check for single vs multiple embeds
$(document).ready(function () {
	const fieldname = 'input[name="arcads_dfp_acm_provider_multiple_embeds[]"]';
	if ($(fieldname).length) {
		const field = $(fieldname);
		const multiple = $(fieldname + ':checked').val();
		showEmbedOptions(field, multiple);
	}
});
