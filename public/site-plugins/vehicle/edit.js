$(document).ready(function () {
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

    // Select2 - Vehicle Type
    $('#vehicle_type').select2({
        placeholder: 'Select Vehicle Type',
        allowClear: true,
        minimumInputLength: 1,  // Prevents the request until 1 or more characters are entered
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';  // You can customize this message
            },
            noResults: function () {
                return 'No data found';  // No data found message
            }
        },
        ajax: {
            url: baseUrl + "/vehicle/fetch/vehicle-type",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term // Only send the term if it's longer than 1 character
                };
            },
            processResults: function (response) {
                return {
                    results: response,  // Your response should contain the expected format
                };
            },
            cache: true,
        }
    });

    // Edit Vehicle - Form Validate
    $('#form-edit-vehicle').validate({
        rules: {
            vehicle_type: {
                required: true
            },
            vehicle_name: {
                required: true
            },
            vehicle_dimensions: {
                required: true,
            },
            vehicle_max_load_capacity: {
                required: true,
                number: true,
                min: 0
            },
			vehicle_fixed_km: {
                required: true,
                number: true,
                min: 0
            },
			vehicle_fixed_km_delivery_charge: {
                required: true,
                number: true,
                min: 0
            },
            vehicle_per_km_delivery_charge: {
                required: true,
                number: true,
                min: 0
            },
            vehicle_per_km_extra_delivery_charge: {
                required: true,
                number: true,
                min: 0
            },
        },
        messages: {
            vehicle_type: {
                required: "The vehicle type field is required."
            },
            vehicle_name: {
                required: "The vehicle name field is required."
            },
            vehicle_dimensions: {
                required: "The vehicle dimensions field is required."
            },
            vehicle_max_load_capacity: {
                required: "Please enter the max load capacity",
                number: "Please enter a valid number"
            },
			vehicle_fixed_km: {
                required: "Please enter the fixed km",
                number: "Please enter a valid number"
            },
			vehicle_fixed_km_delivery_charge: {
                required: "Please enter the fixed km delivery charge",
                number: "Please enter a valid number"
            },
            vehicle_per_km_delivery_charge: {
                required: "Please enter the per km delivery charge",
                number: "Please enter a valid number"
            },
            vehicle_per_km_extra_delivery_charge: {
                required: "Please enter the extra delivery charge per km",
                number: "Please enter a valid number"
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
            form.submit();
        }
    });


    // Update Personal Information / Document Verification
    function updated_vehicle(formData) {
        var formUrl = `${baseUrl}/vehicle/${vehicleId}`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#vehicle-update").prop("disabled", true);
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
                        $("#form-edit-vehicle").removeClass("was-invalid");
                        $(".backend-error").remove();
                        setTimeout(() => {
                            window.location.href = `${baseUrl}/vehicle`
                        }, 2000);
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#vehicle-update").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#vehicle-update").removeAttr("disabled");
            },
        });
    }

    $("#vehicle-update").on("click", function (e) {
        if ($("#form-edit-vehicle").valid()) { // Triggers validation
            var formData = new FormData($("#form-edit-vehicle")[0]);
            updated_vehicle(formData);
        }
    });

    $('#vehicle_name').on('input', function () {
        $('#vehicle_name').next('span.backend-error').text('');
    });

    // Vehicle Image OnChange Event
    $('#vehicle_icon').on('change', function (e) {
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

                    // Validate dimensions (for landscape image)
                    if (width !== 250 || height !== 250) {
                        $('#error_message').text('Image must be 250x250 pixels.').show();
                        $('#image_preview').addClass('d-none'); // Hide preview if dimensions are wrong
                        $('#vehicle_image').val(''); // Reset file input
                    } else {
                        $('#error_message').hide(); // Hide error message
                        $('#preview_img').attr('src', img.src); // Set preview image
                        $('#image_preview').removeClass('d-none').show(); // Show image preview
                    }
                };
            };

            reader.readAsDataURL(file); // Read the file
        } else {
            // Hide preview if no file is selected
            $('#image_preview').addClass('d-none').hide();
        }
    });

    // Remove Vehicle Image
    $('#remove_image').on('click', function () {
        let vehicleId = $('#preview_img').data('id'); // Get the vehicle ID from the data-id attribute
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
                            url: '/vehicle/delete/icon/' + vehicleId, // Server-side route to handle image deletion
                            type: 'GET',
                            data: {},
                            success: function (response) {
                                if (response.success) {
                                    // Reset the file input and image preview
                                    $('#vehicle_icon').val(''); // Clear the file input
                                    $('#preview_img').attr('src', '#'); // Reset the image source to #
                                    $('#image_preview').addClass('d-none'); // Hide the preview div with d-none
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
            $('#vehicle_icon').val(''); // Clear the file input
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