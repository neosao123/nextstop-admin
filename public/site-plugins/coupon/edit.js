$(document).ready(function () {
    
	$('#coupon_start_date').flatpickr({
        dateFormat: "d-m-Y",
    });
	
	$('#coupon_end_date').flatpickr({
        dateFormat: "d-m-Y"
    });
	
	// Summary Note
    tinymce.init({
        selector: 'textarea.tinymce', // Use the correct selector for your textarea
        height: 300, // Adjust the height as needed
        menubar: false, // Disable the menu bar if not needed
        branding: false,
        statusbar: false,
        plugins: 'lists link image charmap preview hr', // Add desired plugins
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | preview', // Customize the toolbar
        setup: function (editor) {
            editor.on('change', function () {
                editor.save(); // Update the textarea on change
            });
        }
    });

    // Select2 - Coupon Type
    $('#coupon_type').select2({
        placeholder: 'Select Coupon Type',
        allowClear: true,
    });


    // Edit Vehicle - Form Validate
    $('#form-edit-coupon').validate({
        rules: {
            coupon_code: {
                required: true,
                maxlength: 20 // Example constraint
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
                maxlength: "The coupon code cannot exceed 20 characters."
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
        var formUrl = `${baseUrl}/coupon/${id}`;
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

    $("#coupon-update").on("click", function (e) {
        if ($("#form-edit-coupon").valid()) { // Triggers validation
            var formData = new FormData($("#form-edit-coupon")[0]);
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
                $('#image_preview').addClass('d-none'); // Hide preview if invalid
                return;
            }

            // Validate file size
            if (file.size > maxFileSize) {
                $('#error_message').text("The image size must be less than 2 MB.").show();
                $(this).val(''); // Clear the input
                $('#image_preview').addClass('d-none'); // Hide preview if invalid
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
                    $('#image_preview').removeClass('d-none'); // Show image preview
                };
            };
            reader.readAsDataURL(file);
        } else {
            // Hide preview if no file is selected
            $('#image_preview').addClass('d-none');
        }
    });


    // Remove Vehicle Image
    $('#remove_image').on('click', function () {

        let id = $('#preview_img').data('id'); // Get the vehicle ID from the data-id attribute
        let imageSrc = $('#preview_img').attr('src'); // Get the current src of the image
        // Check if the image source is a URL (for existing image) or just '#' (for new image)
        if (imageSrc && imageSrc !== '#' && imageSrc.includes('storage-bucket')) {
            $.confirm({
                icon: "fa fa-warning",
                title: "Confirm Delete!",
                content: "Are you sure you want to delete this icon?",
                theme: "modern",
                draggable: false,
                type: "red",
                typeAnimated: true,
                buttons: {
                    confirm: function () {
                        $.ajax({
                            url: '/coupon/delete/image/' + id, // Server-side route to handle image deletion
                            type: 'GET',
                            data: {},
                            success: function (response) {
                                if (response.success) {
                                    // Reset the file input and image preview
                                    $('#coupon_image').val(''); // Clear the file input
                                    $('#preview_img').attr('src', '#'); // Reset the image source to #
                                    $('#image_preview').addClass('d-none'); // Hide the preview div with d-none
                                    $('#coupon_image').removeClass('d-none');
                                    $('#accept_format').removeClass('d-none');
                                    toast('Image deleted successfully.', 'success');
                                } else {
                                    toast('Failed to delete the image.', 'error');
                                }
                            },
                            error: function (xhr, status, error) {
                                toast('An error occurred: ' + xhr.responseText, 'error');
                            }
                        });
                    },
                    cancel: function () {
                        // Cancel button action (optional)
                    }
                }
            });
        } else {
            // This is a new image that hasn't been uploaded yet
            $('#coupon_image').val(''); // Clear the file input
            $('#preview_img').attr('src', '#'); // Reset the image source to #
            $('#image_preview').addClass('d-none'); // Hide the preview div with d-none
        }
    });
});
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });

});