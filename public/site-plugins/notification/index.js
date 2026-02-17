$(document).ready(function () {
   
   
     $('#customer_name').parent().hide();
     $('#driver_name').parent().hide();

    // Show the correct dropdown based on selected type
    $('#type').change(function() {
      var selectedType = $(this).val();

      // Hide both dropdowns initially
      $('#customer_name').parent().hide();
      $('#driver_name').parent().hide();

      // Show the correct dropdown based on type selection
      if (selectedType == 'customer') {
        $('#customer_name').parent().show(); // Show customer dropdown
         // Initialize Select2 on customer_name field
    $('#customer_name').select2({
        placeholder: 'Select Customer',
        allowClear: true,
        minimumInputLength: 1,  
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';
            },
            noResults: function () {
                return 'No data found';
            }
        },
        ajax: {
            url: baseUrl + "/notification/fetch/customer",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(item => ({
                        id: item.id,
                        text: item.text
                    }))
                };
            },
            cache: true,
        }
    });
	
	
	  
	  } else if (selectedType == 'driver') {
        $('#driver_name').parent().show(); // Show driver dropdown
      
	    	// Initialize Select2 on driver field
    $('#driver_name').select2({
        placeholder: 'Select Driver',
        allowClear: true,
        minimumInputLength: 1,  
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';
            },
            noResults: function () {
                return 'No data found';
            }
        },
        ajax: {
            url: baseUrl + "/notification/fetch/driver",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(item => ({
                        id: item.id,
                        text: item.text
                    }))
                };
            },
            cache: true,
        }
    });
	  
	  }
    });
   
  

    $('#form-add-notification').validate({
			rules: {
				notification_title: {
					required: true
				},
				type:{
				    required:true
				},
				message: {
					required: true,
				}
			},
			messages: {
				notification_title: {
					required: "Notification title is required."
				},
				type: {
					required: "Type is required."
				},
				message: {
					required: "Message field is required."
				}
			},
			errorElement: 'span',
			errorPlacement: function (error, element) {
				error.addClass('text-danger');
				
				// For the Select2 multi-select field (customer_name[])
				if (element.hasClass('select2') || element.hasClass('select2-hidden-accessible')) {
					// Insert the error message after the Select2 container
					error.insertAfter(element.next('.select2-container'));
				} else {
					// For other input fields, insert error message normally
					error.insertAfter(element);
				}
			},
			submitHandler: function (form) {
				$('.backend-error').remove();
				form.submit();
			}
		});



    // add notification
    function add_notification(formData) {
        var formUrl = `${baseUrl}/notification`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#notification-save").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".error .text-danger").remove();
                    $(".backend-error").remove();
                    $.each(response.errors, function (i, v) {
                        let errorMessage = '<span class="backend-error text-danger">' + v[0] + '</span>';
                        let element = $("[name='" + i + "']");
                        if (element.hasClass('select2-hidden-accessible')) {
                            // Handle select2 error messages
                            element.next('.select2-container').after(errorMessage);
                        } else {
                            element.after(errorMessage);
                        }
                    });
                    return false;
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-add-notification").removeClass("was-invalid");
                        $(".backend-error").remove();
                        setTimeout(() => {
                            window.location.href = `${baseUrl}/notification`
                        }, 2000);
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#notification-save").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#notification-save").removeAttr("disabled");
            },
        });
    }

    $("#notification-save").on("click", function (e) {
        if ($("#form-add-notification").valid()) { // Triggers validation
            var formData = new FormData($("#form-add-notification")[0]);
            add_notification(formData);
        }
    });

    $('#notification_title').on('input', function () {
        $('#notification_title').next('span.backend-error').text('');
    });

    $('#image').on('change', function (e) {
			const file = e.target.files[0];

			// Clear previous error message
			$('#error_message').hide().text('');

			if (file) {
				const fileType = file.type;
				const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png'];

				// Validate file type
				if (!validImageTypes.includes(fileType)) {
					$('#error_message').text("Please upload a valid image file (jpg, jpeg, png).").show();
					$(this).val(''); // Clear the input
					$('#image_preview').hide(); // Hide preview if invalid
					return;
				}

				// Show image preview
				const reader = new FileReader();
				const img = new Image(); // Create a new Image object

				reader.onload = function (event) {
					img.src = event.target.result; // Set the image source

					img.onload = function () {
						// Check dimensions after the image has loaded
						const width = img.width;
						const height = img.height;

						// Optional: Check dimensions if required (e.g., 250x250 pixels)
						// if (width !== 250 || height !== 250) {
						//     $('#error_message').text('Image must be 250x250 pixels.').show();
						//     $('#image_preview').hide();
						//     $('#image').val(''); // Reset file input
						// } else {
							$('#error_message').hide(); // Hide error message if valid
							$('#preview_img').attr('src', img.src); // Set preview image
							$('#image_preview').show(); // Show image preview container
						// }
					};
				};

				reader.readAsDataURL(file); // Trigger file read
			} else {
				// Hide preview if no file is selected
				$('#image_preview').hide();
			}
		});

		// Remove image functionality
		$('#remove_image').on('click', function () {
			$('#image').val(''); // Clear the file input
			$('#preview_img').attr('src', '#'); // Reset the image source
			$('#image_preview').hide(); // Hide the preview div
			$('#error_message').hide().text(''); // Clear error message
		});


});
