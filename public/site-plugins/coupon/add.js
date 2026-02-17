$(document).ready(function () {
    
	$('#coupon_start_date').flatpickr({
        dateFormat: "d-m-Y",
    });
	
	$('#coupon_end_date').flatpickr({
        dateFormat: "d-m-Y"
    });
	
	
	function validateDates() {
        var startDate = $('#start_date').datepicker('getDate');
        var endDate = $('#end_date').datepicker('getDate');

        if (startDate && endDate && startDate > endDate) {
            toast("The End Date should be greater than the Start date.","error");
            $('#start_date').datepicker('setDate', null);
            $('#end_date').datepicker('setDate', null);
        }
    }
	
	
	
	
	tinymce.init({
		selector: 'textarea.tinymce',
		height: 300,
		menubar: false,
		branding: false,
		statusbar: false,
		plugins: 'lists link image charmap preview hr table', // Add 'table' plugin
		toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | preview | table', // Include 'table' in toolbar
		setup: function (editor) {
			editor.on('change', function () {
				editor.save();
			});
		}
	});
    // Select2 - Coupon Type
    $('#coupon_type').select2({
        placeholder: 'Select Coupon Type',
        allowClear: true,
    });


    $('#form-add-coupon').validate({
        rules: {
            coupon_code: {
                required: true,
            },
            coupon_type: {
                required: true
            },
            coupon_amount_or_percentage: {
                required: true,
                number: true,
                min: 0
            },
            coupon_cap_limit: {
                required: true,
                number: true,
                min: 0
            },
            coupon_min_order_amount: {
                required: true,
                number: true,
                min: 0
            },
            coupon_description: {
                maxlength: 500 // Example constraint
            },
        },
        messages: {
            coupon_code: {
                required: "The coupon code field is required.",
            },
            coupon_type: {
                required: "The coupon type field is required."
            },
            coupon_amount_or_percentage: {
                required: "Please enter the coupon amount or percentage.",
                number: "Please enter a valid number.",
                min: "The amount must be greater than or equal to 0."
            },
            coupon_cap_limit: {
                required: "Please enter the coupon cap limit.",
                number: "Please enter a valid number.",
                min: "The cap limit must be greater than or equal to 0."
            },
            coupon_min_order_amount: {
                required: "Please enter the minimum order amount.",
                number: "Please enter a valid number.",
                min: "The amount must be greater than or equal to 0."
            },
            coupon_description: {
                maxlength: "The description cannot exceed 500 characters."
            },
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('text-danger');
            if (element.hasClass('select2') || element.next('.nice-select').length > 0) {
                error.insertAfter(element.next('.select2-container')); // For Select2
            } else {
                error.insertAfter(element); // For other inputs
            }
        },
        submitHandler: function (form) {
            $('.backend-error').remove();
            form.submit();
        }
    });


    // Update Personal Information / Document Verification
    function update_coupon(formData) {
        var formUrl = `${baseUrl}/coupon`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#coupon-save").prop("disabled", true);
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
                        $("#form-add-coupon").removeClass("was-invalid");
                        $(".backend-error").remove();
                        setTimeout(() => {
                            window.location.href = `${baseUrl}/coupon`
                        }, 2000);
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#coupon-save").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#coupon-save").removeAttr("disabled");
            },
        });
    }

    $("#coupon-save").on("click", function (e) {
        if ($("#form-add-coupon").valid()) { // Triggers validation
            var formData = new FormData($("#form-add-coupon")[0]);
            update_coupon(formData);
        }
    });

    $('#coupon_code').on('input', function () {
        $('#coupon_code').next('span.backend-error').text('');
    });
    $('#coupon_amount_or_percentage').on('input', function () {
        $('#coupon_amount_or_percentage').next('span.backend-error').text('');
    });
    $('#coupon_cap_limit').on('input', function () {
        $('#coupon_cap_limit').next('span.backend-error').text('');
    });
    $('#coupon_min_order_amount').on('input', function () {
        $('#coupon_min_order_amount').next('span.backend-error').text('');
    });

    $('#coupon_image').on('change', function (e) {
        const file = e.target.files[0];

        // Clear previous error message
        $('#error_message').hide().text('');

        if (file) {
            const fileType = file.type;
            const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            const maxFileSize = 2 * 1024 * 1024; // 2 MB in bytes

            // Validate file type
            if (!validImageTypes.includes(fileType)) {
                $('#error_message').text("Please upload a valid image file (jpg, jpeg, png).").show();
                $(this).val(''); // Clear the input
                $('#image_preview').hide(); // Hide preview if invalid
                return;
            }

            // Validate file size
            if (file.size > maxFileSize) {
                $('#error_message').text("The image size must be less than 2 MB.").show();
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
                    $('#error_message').hide(); // Hide error message
                    $('#preview_img').attr('src', img.src); // Set preview image
                    $('#image_preview').show(); // Show image preview
                };
            };
            reader.readAsDataURL(file);
        } else {
            // Hide preview if no file is selected
            $('#image_preview').hide();
        }
    });

    // Remove image functionality
    $('#remove_image').on('click', function () {
        $('#coupon_image').val(''); // Clear the file input
        $('#preview_img').attr('src', '#'); // Reset the image source
        $('#image_preview').hide(); // Hide the preview div
        $('#error_message').hide().text(''); // Clear error message
    });

});
