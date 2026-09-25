jQuery(function ($) {
	let videoFrame;

	const videoInput = $('#vehicle_hover_video');
	const videoPreview = $('#vehicle_hover_video_preview');
	const selectButton = $('#select_vehicle_hover_video');
	const removeButton = $('#remove_vehicle_hover_video');

	selectButton.on('click', function (event) {
		event.preventDefault();

		if (videoFrame) {
			videoFrame.open();
			return;
		}

		videoFrame = wp.media({
			title: 'Select Vehicle Hover Video',
			button: {
				text: 'Use this video'
			},
			library: {
				type: 'video'
			},
			multiple: false
		});

		videoFrame.on('select', function () {
			const attachment = videoFrame
				.state()
				.get('selection')
				.first()
				.toJSON();

			videoInput.val(attachment.id);

			videoPreview.html(`
				<video
					src="${attachment.url}"
					controls
					muted
					style="width:100%;max-width:500px;margin-bottom:10px;"
				></video>
			`);

			removeButton.show();
		});

		videoFrame.open();
	});

	removeButton.on('click', function (event) {
		event.preventDefault();

		videoInput.val('');
		videoPreview.empty();
		removeButton.hide();
	});
});