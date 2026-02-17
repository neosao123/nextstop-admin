$(document).ready(function () {
// Custom method to validate file size
      $.validator.addMethod("fileSize", function(value, element, param) {
        if (element.files && element.files[0]) {
          return this.optional(element) || (element.files[0].size <= param * 1024 * 1024);
        }
        return true;
      }, "File size must be less than {0} MB.");

      // Custom method to validate file extension
      $.validator.addMethod("extension", function(value, element, param) {
        param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
        return this.optional(element) || value.match(new RegExp("\\.(" + param + ")$", "i"));
      }, "Please enter a value with a valid extension.");

      // Form validation
      $('#form-edit-training-video').validate({
        ignore: "",
        rules: {
          video_title: {
            required: true,
            minlength: 2,
            maxlength: 150
          },
          video: {
            extension: "mp4|avi|mov|wmv|flv|mkv|webm",
            fileSize: 4
          },
          thumbnail: {
            extension: "jpg|jpeg|png"
          },
          total_video_time_length: {
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
            extension: "Please upload a video file (MP4, AVI, MOV, WMV, FLV, MKV, or WEBM format).",
            fileSize: "The video file size must be less than 4 MB."
          },
          thumbnail: {
            extension: "Please upload an image file (JPG, PNG, or JPEG format)."
          },
          total_video_time_length: {
            integer: "Please enter a valid integer."
          }
        },
        errorPlacement: function(error, element) {
          error.addClass('text-danger');
          error.insertAfter(element);
        }
      });

      // Clear backend error messages on input
      $('#video_title, #total_video_time_length').on('input', function() {
        $(this).next('span.backend-error').text('');
      });
});
