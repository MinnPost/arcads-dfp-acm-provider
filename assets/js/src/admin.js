var $ = window.jQuery;

function showEmbedOptions(field, multiple) {
	if ( multiple == '0' ) {
		$(field).parents('form').find('h2:nth-of-type(2)').hide();
		$(field).parents('form').find('h2:nth-of-type(3)').show();
		$(field).parents('form').find('table:nth-of-type(2)').hide();
		$(field).parents('form').find('table:nth-of-type(3)').show();
	} else if ( multiple == '1' ) {
		$(field).parents('form').find('h2:nth-of-type(2)').show();
		$(field).parents('form').find('h2:nth-of-type(3)').hide();
		$(field).parents('form').find('table:nth-of-type(2)').show();
		$(field).parents('form').find('table:nth-of-type(3)').hide();
	}
}

// as the drupal plugin does, we only allow one field to be a prematch or key
$(document).on('click', 'input[name="arcads_dfp_acm_provider_multiple_embeds[]"]', function() {
	var multiple = $(this).val();
	showEmbedOptions($(this), multiple);
});

$(document).ready(function() {
	var fieldname = 'input[name="arcads_dfp_acm_provider_multiple_embeds[]"]';
	if ( $(fieldname).length) {
		var field = $(fieldname);
		var multiple = $(fieldname + ':checked').val();
		showEmbedOptions(field, multiple);
	}
});
