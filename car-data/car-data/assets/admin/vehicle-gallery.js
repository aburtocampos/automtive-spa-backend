jQuery(function ($) {
	'use strict';

	const $input = $('#vehicle_gallery');
	const $preview = $('#vehicle-gallery-preview');
	const $selectButton = $('#vehicle-gallery-select');
	const $clearButton = $('#vehicle-gallery-clear');

	if (!$input.length) {
		return;
	}

	let mediaFrame = null;

	function getSelectedIds() {
		const value = $input.val();

		if (!value) {
			return [];
		}

		return value
			.split(',')
			.map(Number)
			.filter(Boolean);
	}

	function updateInput() {
		const ids = [];

		$preview
			.find('.automotive-gallery__item')
			.each(function () {
				ids.push($(this).data('id'));
			});

		$input.val(ids.join(','));
	}

	$selectButton.on('click', function (event) {
		event.preventDefault();

		if (mediaFrame) {
			mediaFrame.open();
			return;
		}

		mediaFrame = wp.media({
			title: 'Select Vehicle Images',
			button: {
				text: 'Use selected images'
			},
			library: {
				type: 'image'
			},
			multiple: true
		});

		mediaFrame.on('open', function () {
			const selection = mediaFrame.state().get('selection');

			getSelectedIds().forEach(function (attachmentId) {
				const attachment = wp.media.attachment(attachmentId);

				attachment.fetch();
				selection.add(attachment);
			});
		});

		mediaFrame.on('select', function () {
			const selection = mediaFrame
				.state()
				.get('selection')
				.toJSON();

			$preview.empty();

			selection.forEach(function (attachment) {
				const thumbnail =
					attachment.sizes?.thumbnail?.url ||
					attachment.sizes?.medium?.url ||
					attachment.url;

				const $item = $('<div>', {
					class: 'automotive-gallery__item',
					'data-id': attachment.id
				});

				const $image = $('<img>', {
					src: thumbnail,
					alt: attachment.alt || '',
					class: 'automotive-gallery__image'
				});

				const $remove = $('<button>', {
					type: 'button',
					class: 'automotive-gallery__remove',
					'aria-label': 'Remove image',
					text: '×'
				});

				$item.append($image, $remove);
				$preview.append($item);
			});

			updateInput();
		});

		mediaFrame.open();
	});

	$preview.on(
		'click',
		'.automotive-gallery__remove',
		function (event) {
			event.preventDefault();

			$(this)
				.closest('.automotive-gallery__item')
				.remove();

			updateInput();
		}
	);

	$clearButton.on('click', function (event) {
		event.preventDefault();

		$preview.empty();
		$input.val('');
	});
});