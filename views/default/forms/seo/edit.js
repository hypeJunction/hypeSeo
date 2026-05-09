import elgg from 'elgg';
import $ from 'jquery';
import spinner from 'elgg/spinner';
import lightbox from 'elgg/lightbox';

$(document).on('submit', '.elgg-form-seo-edit', function (e) {
	var $form = $(this);
	if ($form.closest('#colorbox').length === 0) {
		return;
	}

	e.preventDefault();
	elgg.action($form.attr('action'), {
		data: $form.serialize(),
		beforeSend: spinner.start,
		complete: spinner.stop,
		success: lightbox.close
	});
});
