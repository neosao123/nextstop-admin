$(document).ready(function () {
    // Custom method to validate file size
    $.validator.addMethod("fileSize", function (value, element) {
        if (element.files && element.files[0]) {
            const fileSize = element.files[0].size; // size in bytes
            return this.optional(element) || (fileSize <= 4 * 1024 * 1024); // 4 MB
        }
        return true; // Allow if no file is selected
    }, "File size must be less than 4 MB.");

    // Custom method to validate integer
    $.validator.addMethod("integer", function (value) {
        return this.optional(value) || /^[0-9]+$/.test(value);
    }, "Please enter a valid integer.");

	  $('#form-add-training-video').validate({
		ignore: "",
		rules: {
			video_title: {
				required: true,
				minlength: 2,
				maxlength: 150,
				alphanumeric: true
			},
			video: {
				required: true,
				extension: "mp4,avi,mov,wmv,flv,mkv,webm",
				fileSize: true
			},
			thumbnail: {
				required: true,
				extension: "jpg,png,jpeg",
				fileSize: true
			},
			total_video_time_length: {
				required: true,
				integer: true
			}
		},
		messages: {
			video_title: {
				required: "The video title field is required.",
				minlength: "The video title must be at least 2 characters long.",
				maxlength: "The video title cannot exceed 150 characters."
			},
			video: {
				required: "The video file is required.",
				extension: "Please upload a video file (mp4, avi, mov, wmv, flv, mkv, or webm format).",
				fileSize: "The video file size must be less than 4 MB."
			},
			thumbnail: {
				required: "The thumbnail image is required.",
				extension: "Please upload an image file (jpg, png, or jpeg format).",
				fileSize: "The thumbnail image size must be less than 2 MB."
			},
			total_video_time_length: {
				required: "Total video time length is required.",
				integer: "Please enter a valid integer."
			}
		},
		errorPlacement: function (error, element) {
			error.addClass('text-danger');
			error.insertAfter(element);
		},
	});

    // Clear backend error message on input
    $('#video_title').on('input', function () {
        $('#video_title').next('span.backend-error').text('');
    });

    $('#total_video_time_length').on('input', function () {
        $('#total_video_time_length').next('span.backend-error').text('');
    });
});
