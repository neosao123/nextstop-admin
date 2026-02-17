$(document).ready(function () {

    // Custom method for regex validation
    $.validator.addMethod("regex", function (value, element, param) {
        return this.optional(element) || param.test(value);
    }, "Please check your input.");

    // Custom method for document number validation
    $.validator.addMethod("documentNumberValidation", function (value, element) {
        const documentType = $(element).closest('.row').find("select[name='document_type[]']").val();
        let regex;

        switch (documentType) {
            case 'aadhar_card':
                regex = /^\d{4}\s\d{4}\s\d{4}$/; // Format: 1234 5678 9012
                break;
            case 'pan_card':
                regex = /^[A-Z]{5}\d{4}[A-Z]$/; // Format: ABCDE1234F
                break;
            case 'driving_license':
                regex = /^[A-Z]{2}\d{2} \d{4}\d{7}$/; // Example format: AB12CD3456
                break;
            case 'bank_passbook':
                regex = /^[0-9]+$/; // Numeric values
                break;
            default:
                return true; // No specific validation needed
        }
        return this.optional(element) || regex.test(value);
    }, "Invalid document number format for the selected document type.");

    // Custom method to check document types
    $.validator.addMethod("checkDocumentType", function (value, element) {
        const allowedTypes = ['aadhar_card', 'pan_card', 'driving_license', 'bank_passbook'];
        return this.optional(element) || allowedTypes.includes(value);
    }, "Please select a valid document type.");

    // Custom method to validate that the input value is within a specific set of values
    $.validator.addMethod("inValues", function (value, element, param) {
        return param.includes(value);
    });

    // Initialize form validation
    $('#form-driver-personal-information').validate({
        rules: {
            driver_first_name: {
                required: true,
                minlength: 2,
                maxlength: 150,
                regex: /^[A-Za-z\s]+$/
            },
            driver_last_name: {
                required: true,
                minlength: 2,
                maxlength: 150,
                regex: /^[A-Za-z\s]+$/
            },
            driver_email: {
                email: true,
                minlength: 2,
                maxlength: 150,
                regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
            },
            driver_phone: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10,
            },
            driver_gender: {
                required: true,
                // Custom rule to check if the selected value is one of the allowed values
                inValues: ["male", "female", "others"]
            },
            driver_serviceable_location: {
                required: true,
            },
            driver_bank_name: {
                required: true,
                regex: /^[A-Za-z\s]+$/
            },
            driver_bank_account_number: {
                required: true,
                number: true,
                regex: /^\d{9,18}$/
            },
            driver_bank_ifsc_code: {
                required: true,
                regex: /^[A-Z]{4}0[A-Z0-9]{6}$/
            },
            driver_bank_branch_name: {
                required: true,
                maxlength: 255
            },
            personal_verification_status: { required: true },
            personal_verification_reason: {
                required: function () {
                    return $('#personal_verification_status').val() === 'reject';
                }
            },
        },
        messages: {
            driver_first_name: {
                required: "The first name field is required.",
                minlength: "The first name must be at least 2 characters long.",
                maxlength: "The first name cannot exceed 150 characters.",
                regex: "The first name can contain only letters and spaces."
            },
            driver_last_name: {
                required: "The last name field is required.",
                minlength: "The last name must be at least 2 characters long.",
                maxlength: "The last name cannot exceed 150 characters.",
                regex: "The last name can contain only letters and spaces."
            },
            driver_email: {
                email: "Please enter a valid email address.",
                minlength: "The email must be at least 2 characters long.",
                maxlength: "The email cannot exceed 150 characters.",
                regex: "Please enter a valid email format."
            },
            driver_serviceable_location: {
                required: "The servicable location field is required.",
            },
            driver_phone: {
                required: "The phone number field is required.",
                digits: "The phone number must be exactly 10 digits.",
                minlength: "The phone number must be exactly 10 digits",
                maxlength: "The phone number must be exactly 10 digits"
            },
            driver_gender: {
                required: "The gender field is required.",
                inValues: "The selected gender must be male, female, or others."
            },
            driver_bank_name: {
                required: "The bank name field is required.",
                regex: "The bank name can contain only letters and spaces."
            },
            driver_bank_account_number: {
                required: "The bank account number field is required.",
                number: "The bank account number must be numeric.",
                regex: "The bank account number must be between 9 to 18 digits."
            },
            driver_bank_ifsc_code: {
                required: "The IFSC code field is required.",
                regex: "The IFSC code must be in the valid format."
            },
            driver_bank_branch_name: {
                required: "The branch name field is required.",
                maxlength: "The branch name cannot exceed 255 characters."
            },
            personal_verification_status: { required: "Please select a verification status." },
            personal_verification_reason: { required: "Please provide a reason for rejection." },
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('text-danger');
            if (element.hasClass('select2')) {
                error.insertAfter(element.next('.select2-container'));
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            $('.backend-error').remove();
            form.submit();
        }
    });

    // Toggle between edit and view mode
    $('#edit_btn').on('click', function () {
        $(this).addClass('d-none');
        $('#view_btn').removeClass('d-none');
        $('.toggleElement').prop('readonly', false);
        $('.deletePersonalDocument').removeClass('d-none');
        $('.deleteVehicleDocument').removeClass('d-none');
        $('.deletedriverPhoto').removeClass('d-none');
        $('.uploadDocs').removeClass('d-none');
        $('#verify_btn').addClass('d-none');
        $('#update_btn').removeClass('d-none');
        $('.verification_status').addClass('d-none');
        $('#driver_gender_input').addClass('d-none');
        $('#driver_gender_div').removeClass('d-none');
        $('#driver_serviceable_location_input').addClass('d-none');
        $('#driver_serviceable_location_div').removeClass('d-none');
        $('#driver_is_active').attr('onclick', 'return true');
        $('#form-driver-personal-information').attr('action', '');
        $("input[name='document_number[]']").removeAttr("readonly");
        $("input[name='vehicle_document_number[]']").removeAttr("readonly");
        $(".photo-label").addClass('d-none');
    });

    $('#view_btn').on('click', function () {
        window.location.reload();
    });

    $('.file-upload').change(function (e) {
        var fileInput = $(this);
        var file = fileInput[0].files[0];
        var key = $(this).data('key');
        console.log(key);
        // Define allowed file types
        var allowedExtensions = /(\.pdf|\.docx|\.jpeg|\.jpg|\.png)$/i;

        // Check if a file is selected
        if (file) {
            // Validate file type
            if (!allowedExtensions.exec(file.name)) {
                // Show error message
                fileInput.val('');
                toast('Invalid file type. Only PDF, DOCX, JPEG, JPG, and PNG files are allowed.', 'error');
            }
            // Validate file size (10MB = 10 * 1024 * 1024 bytes)
            else if (file.size >= 10 * 1024 * 1024) {
                // Show error message
                fileInput.val('');
                toast('File size must be less than equals to 10MB.', 'error');
            }
            else {
                // Show the preview based on the file type
                var filePath = URL.createObjectURL(file);
                var fileName = file.name;

                // Clear previous content
                $('.file-preview-container').empty();

                if (/\.(jpeg|jpg|png)$/i.test(file.name)) {
                    // If the file is an image, show it
                    $('.file-preview-container').append('<img class="img-radius" src="' + filePath + '" alt="Uploaded Image" height="60" width="60" />');
                } else if (/\.(pdf|docx)$/i.test(file.name)) {
                    // If the file is a PDF or DOCX, show the file name
                    $('.file-preview-container').append('<p>' + fileName + '</p>');
                }
            }
        } else {
            // No file selected, clear the preview
            $('.file-preview-container').empty();
        }
    });

    $('#driver_photo').change(function (e) {
        var driverPhoto = $(this);
        var file = driverPhoto[0].files[0];
        // Define allowed file types
        var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;

        // Check if a file is selected
        if (file) {
            // Validate file type
            if (!allowedExtensions.exec(file.name)) {
                // Show error message
                driverPhoto.val('');
                toast('Invalid file type. Only PDF, DOCX, JPEG, JPG, and PNG files are allowed.', 'error');
            }
            // Validate file size (10MB =10 * 1024 * 1024 bytes)
            else if (file.size >= 10 * 1024 * 1024) {
                // Show error message
                driverPhoto.val('');
                toast('File size must be less than equals to 10MB.', 'error');
            }
            else {
                // Show the preview based on the file type
                var filePath = URL.createObjectURL(file);
                var fileName = file.name;

                // Clear previous content
                $('.file-preview-container').empty();

                if (/\.(jpeg|jpg|png)$/i.test(file.name)) {
                    // If the file is an image, show it
                    $('.file-preview-container').append('<img class="img-radius" src="' + filePath + '" alt="Uploaded Image" height="60" width="60" />');
                }
            }
        } else {
            // No file selected, clear the preview
            $('.file-preview-container').empty();
        }
    });

    // Update Personal Information / Document Verification
    function update_personal_info(formData) {
        var formUrl = `${baseUrl}/driver/pending/${driverId}`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#update_btn").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".error .text-danger").remove();
                    $(".backend-error").remove();
                    $.each(response.errors, function (i, v) {
                        let errorMessage = '<div class="backend-error text-danger">' + v[0] + '</div>';
                        let element = $("[name='" + i + "']");
                        if (element.hasClass('select2-hidden-accessible')) {
                            // Handle select2 error messages
                            element.next('.select2-container').after(errorMessage);
                        } else {
                            element.after(errorMessage);
                        }
                    });

                    $.each(response.errors, function (field, message) {
                        // Check if it's a `document_number` or `document_upload` error
                        let isDocumentNumber = field.startsWith('document_number');
                        let isDocumentUpload = field.startsWith('document_');

                        if (isDocumentNumber || isDocumentUpload) {
                            let fieldParts = field.split('_');
                            let documentCount = isDocumentNumber ? '' : fieldParts[1]; // For `document_number`, we label as `number`; for upload, it's `1` or `2`
                            let key = field.split('.')[1]; // Extract '0', '1', etc., to identify the document index

                            // Determine the error element based on document type and index
                            let errorElement;
                            if (isDocumentNumber) {
                                errorElement = $(".document-number-error-" + key); // Assuming a class structure like `document-number-error-0`
                            } else {
                                errorElement = $(".document-" + documentCount + "-upload-error-" + key); // For uploads, like `document-1-upload-error-0`
                            }

                            // Inject the error message if the element exists
                            if (errorElement.length > 0) {
                                errorElement.html('<div class="backend-error text-danger">' + message[0] + '</div>'); // Insert the error message
                            }
                        }
                    });

                    $.each(response.errors, function (field, message) {
                        // Identify if error is for document number or upload
                        let isDocumentNumber = field.startsWith('vehicle_document_number');
                        let isDocumentUpload = field.startsWith('vehicle_document_upload');

                        if (isDocumentNumber || isDocumentUpload) {
                            // Extract index from field name
                            let fieldParts = field.split('.');
                            let key = fieldParts[1]; // Gets the index (e.g., 0, 1, etc.)

                            // Determine the error container based on field type
                            let errorElement;
                            if (isDocumentNumber) {
                                errorElement = $(".vehicle-document-number-error-" + key); // For document number errors
                            } else if (isDocumentUpload) {
                                errorElement = $(".vehicle-document-upload-error-" + key); // For document upload errors
                            }

                            // Insert error message into the identified container
                            if (errorElement.length > 0) {
                                errorElement.html('<div class="backend-error text-danger">' + message[0] + '</div>');
                            }
                        }
                    });


                    return false;
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-driver-personal-information").removeClass("was-invalid");
                        $(".backend-error").remove();
                        setTimeout(() => {
                            window.location.reload()
                        }, 2000);
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                $("#update_btn").removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                $("#update_btn").removeAttr("disabled");
            },
        });
    }

    $("#update_btn").on("click", function (e) {
        e.preventDefault();
        if ($("#form-driver-personal-information").valid()) { // Triggers validation
            var formData = new FormData($("#form-driver-personal-information")[0]);
            update_personal_info(formData);
        }
    });


    $('#driver_email').on('input', function () {
        $('#driver_email').next('span.backend-error').text('');
    });

    $('#driver_first_name').on('input', function () {
        $('#driver_first_name').next('span.backend-error').text('');
    });

    $('#driver_last_name').on('input', function () {
        $('#driver_last_name').next('span.backend-error').text('');
    });

    $('#driver_phone').on('input', function () {
        $('#driver_phone').next('span.backend-error').text('');
    });

    $('#driver_bank_account_number').on('input', function () {
        $('#driver_bank_account_number').next('span.backend-error').text('');
    });

    $('#driver_bank_name').on('input', function () {
        $('#driver_bank_name').next('span.backend-error').text('');
    });

    $('#driver_bank_ifsc_code').on('input', function () {
        $('#driver_bank_ifsc_code').next('span.backend-error').text('');
    });

    $('#driver_bank_branch_name').on('input', function () {
        $('#driver_bank_branch_name').next('span.backend-error').text('');
    });

    // Personal document validation
    $('.document_number').on('input', function () {
        let index = $(this).data('key');
        $(".document-number-error-" + index + " .backend-error").text('');
        var document_type = $(this).data('document-type');
        var document_number = $(this).val();
        var regex;
        var errorMessage;

        switch (document_type) {
            case 'pan_card':
                regex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
                errorMessage = 'Invalid PAN card format. Must be 5 uppercase letters, followed by 4 digits, and 1 uppercase letter (e.g., ABCDE1234F).';
                break;

            case 'aadhar_card':
                regex = /^\d{12}$/;
                errorMessage = 'Invalid Aadhar card format. Must be exactly 12 digits, with no spaces or letters.';
                break;

            case 'driving_license':
                regex = /^[A-Z]{2}\d{2} \d{4}\d{7}$/;
                errorMessage = 'Invalid Driving License format. Must follow the format: 2 uppercase letters, 2 digits, a space, 4 digits, and 7 digits (e.g., MH12 34567890123).';
                break;

            case 'bank_passbook_or_cancel_cheque':
                regex = /^.*$/;
                errorMessage = '';
                break;

            default:
                regex = /.*/;
                errorMessage = 'Invalid format for the selected document type.';
        }

        // Validate input based on regex
        if (regex.test(document_number)) {
            $(this).next('.error-message').addClass('d-none').text('');
        } else {
            if (!$(this).next('.error-message').length) {
                $(this).after('<div class="text-danger error-message"></div>');
            }
            $(this).next('.error-message').removeClass('d-none').text(errorMessage);
        }

        // Check if all fields are valid
        checkAllFields();
    });

    function checkAllFields() {
        let allValid = true;

        $('.document_number').each(function () {
            var document_type = $(this).data('document-type');
            var document_number = $(this).val();
            var regex;
            console.log('personal-doc', document_type, document_number);
            switch (document_type) {
                case 'pan_card':
                    regex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
                    break;
                case 'aadhar_card':
                    regex = /^\d{12}$/;
                    break;
                case 'driving_license':
                    regex = /^[A-Z]{2}\d{2} \d{4}\d{7}$/;
                    break;
                case 'bank_passbook_or_cancel_cheque':
                    regex = /^$/;
                    break;
                default:
                    regex = /.*/;
            }

            if (document_type !== 'bank_passbook_or_cancel_cheque' && (!regex.test(document_number) || document_number.trim() === '')) {
                allValid = false;
                return false; // Break out of loop if invalid
            }
        });

        console.log('personal-doc', allValid);
        // Enable or disable the update button
        $('#update_btn').prop('disabled', !allValid);
    }

    // Vehicle document validation
    $('.vehicle_document_number').on('input', function () {
        let index = $(this).data('key');
        $(".vehicle-document-number-error-" + index + " .backend-error").text('');
        var vehicle_document_type = $(this).data('document-type');
        var vehicle_document_number = $(this).val();
        var regex;
        var errorMessage;

        switch (vehicle_document_type) {
            case 'rc_book':
                regex = /^[A-Z]{2} \d{2} [A-Z]{2} \d{4}$/;
                errorMessage = "The RC Book number format is invalid";
                break;
            case 'insurance':
                regex = /^[A-Z0-9]{5,}$/;
                errorMessage = "The Insurance number format is invalid";
                break;
            default:
                regex = /.*/;
                errorMessage = 'Invalid format.';
        }

        // Validate input based on regex
        if (regex.test(vehicle_document_number)) {
            $(this).next('.error-message').addClass('d-none').text('');
        } else {
            if (!$(this).next('.error-message').length) {
                $(this).after('<div class="text-danger error-message"></div>');
            }
            $(this).next('.error-message').removeClass('d-none').text(errorMessage);
        }

        // Check if all fields are valid
        checkAllVehicleFields();
    });

    function checkAllVehicleFields() {
        let allValid = true;

        $('.vehicle_document_number').each(function () {
            var vehicle_document_type = $(this).data('document-type');
            var vehicle_document_number = $(this).val();
            var regex;

            switch (vehicle_document_type) {
                case 'rc_book':
                    regex = /^[A-Z]{2} \d{2} [A-Z]{2} \d{4}$/;
                    break;
                case 'insurance':
                    regex = /^[A-Z0-9]{5,}$/;
                    break;
                default:
                    regex = /.*/;
            }

            if (!regex.test(vehicle_document_number) || vehicle_document_number.trim() === '') {
                allValid = false;
                return false; // Break out of loop if invalid
            }
        });
        console.log('vehicle-doc', !allValid);
        // Enable or disable the update button
        $('#update_btn').prop('disabled', !allValid);
    }
	
	
	  $('#admin_verification_status').select2({
			placeholder: "Select Status",
			allowClear: true,
		}).on('select2:select', function (e) {
			handleAdminVerificationStatusChange(e.params.data.id);
		}).on('select2:clear', function () {
			handleAdminVerificationStatusChange(null);
		});

		function handleAdminVerificationStatusChange(verificationStatus) {
			if (verificationStatus === 'reject') {
				$('.admin_verification_reason_required').removeClass('d-none');
			} else {
				$('.admin_verification_reason_required').addClass('d-none');
			}
		}

		// Ensure the correct class is shown on page load if 'reject' is pre-selected
		handleAdminVerificationStatusChange($('#admin_verification_status').val());
	

    $('#personal_verification_status').select2({
        placeholder: "Select Status",
        allowClear: true,
    }).on('select2:select', function (e) {
        handleVerificationStatusChange(e.params.data.id); // Pass selected value
    }).on('select2:clear', function () {
        handleVerificationStatusChange(null); // Handle clear action
    });

    $('#driver_gender').select2({
        placeholder: "Select Gender",
        allowClear: true,
    })

    function handleVerificationStatusChange(verificationStatus) {
        if (verificationStatus === 'reject') {
            // Show asterisk if 'reject' is selected
            $('.personal_verification_reason_required').removeClass('d-none');
        } else {
            // Remove asterisk if 'approve' is selected or when cleared
            $('.personal_verification_reason_required').addClass('d-none');
        }
    }

    $('.view-document-btn').on('click', function () {
        var documentUrl = $(this).data('url');
        var documentType = $(this).data('document-type').toLowerCase();

        if (['jpg', 'jpeg', 'png'].includes(documentType)) {
            // Display image in the image modal
            console.log(documentUrl);
            $('#documentImage').attr('src', documentUrl);
            $('#documentModal').modal('show');
        } else if (documentType === 'pdf') {
            // Display PDF in the iframe
            $('#documentIframe').attr('src', documentUrl);
            $('#documentModalPDF').modal('show');
        } else if (documentType === 'docx') {
            // Display DOCX in the iframe using Google Docs viewer
            $('#documentIframe').attr('src', 'https://docs.google.com/gview?url=' + documentUrl + '&embedded=true');
            $('#documentModalPDF').modal('show');
        } else {
            alert('Unsupported file type!');
        }
    });

    // Clear the image source when the image modal is hidden
    $('#documentModal').on('hidden.bs.modal', function () {
        $('#documentImage').attr('src', '');
    });

    // Clear the iframe source when the PDF modal is hidden
    $('#documentModalPDF').on('hidden.bs.modal', function () {
        $('#documentIframe').attr('src', '');
    });

    $('.view-vehicle-document-btn').on('click', function () {
        var documentUrl = $(this).data('url');
        var documentType = $(this).data('document-type').toLowerCase();

        if (['jpg', 'jpeg', 'png'].includes(documentType)) {
            // Display image in the image modal
            $('#documentVehicleImage').attr('src', documentUrl);
            $('#documentVehicleModal').modal('show');
        } else if (documentType === 'pdf') {
            // Display PDF in the iframe
            $('#documentVehicleIframe').attr('src', documentUrl);
            $('#documentVehicleModalPDF').modal('show');
        } else if (documentType === 'docx') {
            // Display DOCX in the iframe using Google Docs viewer
            $('#documentVehicleIframe').attr('src', 'https://docs.google.com/gview?url=' + documentUrl + '&embedded=true');
            $('#documentVehicleModalPDF').modal('show');
        } else {
            alert('Unsupported file type!');
        }
    });

    // Clear the image source when the image modal is hidden
    $('#documentVehicleModal').on('hidden.bs.modal', function () {
        $('#documentVehicleImage').attr('src', '');
    });

    // Clear the iframe source when the PDF modal is hidden
    $('#documentVehicleModalPDF').on('hidden.bs.modal', function () {
        $('#documentVehicleIframe').attr('src', '');
    });

    $('.deletePersonalDocument').on('click', function () {
        const url = $(this).data("url");
        const document_count = $(this).data("document-count");

        $.confirm({
            icon: "fa fa-warning",
            title: "Confirm Delete!",
            content: "Do you want to delete this personal document?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            '_token': csrfToken, // Include CSRF token,
                            'document_count': document_count
                        },
                        success: function (response) {
                            if (response.success) {
                                console.log('data');
                                toast("Personal document deleted successfully.", "success");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while deleting the personal document.", "error");
                            }
                        },
                        error: function (xhr) {
                            // Generic error handler for any server-side errors
                            const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : "An error occurred while processing your request.";
                            toast(errorMessage, "error");
                        }
                    });
                },
                cancel: function () {
                    // Cancel button action (optional)
                }
            }
        });
    });

    $('.deletedriverPhoto').on('click', function () {
        const url = $(this).data("url");

        $.confirm({
            icon: "fa fa-warning",
            title: "Confirm Delete!",
            content: "Do you want to delete this photo?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            '_token': csrfToken, // Include CSRF token,
                        },
                        success: function (response) {
                            if (response.success) {
                                console.log('data');
                                toast("Photo deleted successfully.", "success");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while deleting the photo.", "error");
                            }
                        },
                        error: function (xhr) {
                            // Generic error handler for any server-side errors
                            const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : "An error occurred while processing your request.";
                            toast(errorMessage, "error");
                        }
                    });
                },
                cancel: function () {
                    // Cancel button action (optional)
                }
            }
        });
    });

    // Custom method to validate using regex pattern
    $.validator.addMethod("regex", function (value, element, regexp) {
        let re = new RegExp(regexp);
        return this.optional(element) || re.test(value);
    });

    $('#form-driver-vehicle-information').validate({
        rules: {
            driver_vehicle_number: {
                required: true,
                minlength: 2,
                maxlength: 15,
                regex: /^[A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{4}$/,
            },
            driver_vehicle: {
                required: true,
            },

            vehicle_verification_status: { required: true },
            vehicle_verification_reason: {
                required: function () {
                    return $('#vehicle_verification_status').val() === 'reject';
                }
            },

        },
        messages: {
            driver_vehicle_number: {
                required: "The vehicle number field is required.",
                minlength: "The vehicle number must be at least 2 characters long.",
                maxlength: "The vehicle number cannot exceed 15 characters.",
                regex: "The vehicle number must be in a valid format (e.g., MH12AB1234)."
            },
            driver_vehicle: {
                required: "The vehicle type field is required."
            },
            vehicle_verification_status: { required: "Please select a verification status." },
            vehicle_verification_reason: { required: "Please provide a reason for rejection." },

        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('text-danger');
            if (element.hasClass('select2')) {
                error.insertAfter(element.next('.select2-container'));
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            $('backend-error').remove();
            form.submit();
        }
    });

    $('#driver_vehicle_number').on('input', function () {
        $('#driver_vehicle_number').next('span.backend-error').text('');
    });

    $('#driver_vehicle_type').on('input', function () {
        $('#driver_vehicle_type').next('span.backend-error').text('');
    });

    // Toggle between edit and view mode
    $('#edit_vehicle_info_btn').on('click', function () {
        $(this).addClass('d-none');
        $('#view_vehicle_info_btn').removeClass('d-none');
        $('.toggleVehicleElement').prop('readonly', false);
        $('.toggleVehicleElement').prop('disabled', false);
        $('.deleteVehiclePhoto').removeClass('d-none');
        $('.uploadVehicleDocs').removeClass('d-none');
        $('#verify_vehicle_info_btn').addClass('d-none');
        $('#update_vehicle_info_btn').removeClass('d-none');
        $('.vehicle_verification_status').addClass('d-none');
        $('#form-driver-personal-information').attr('action', '');
        $("input[name='document_number[]']").removeAttr("readonly");
        $(".photo-label").addClass('d-none');

        $('#driver_vehicle_input').addClass('d-none');
        $('#driver_vehicle_div').removeClass('d-none');
    });

    $('#view_vehicle_info_btn').on('click', function () {
        window.location.reload();
    });

    $("#update_vehicle_info_btn").on("click", function (e) {
        e.preventDefault();
        if ($("#form-driver-vehicle-information").valid()) { // Triggers validation
            var formData = new FormData($("#form-driver-vehicle-information")[0]);
            update_vehicle_info(formData);
        }
    });

    function update_vehicle_info(formData) {
        var formUrl = `${baseUrl}/driver/pending/vehicle-information/${driverId}`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#update_vehicle_info_btn").prop("disabled", true);
            },
            success: function (response) {
                $(".text-danger").remove();
                $(".backend-error").remove();

                if (response.hasOwnProperty("errors")) {
                    $.each(response.errors, function (field, messages) {
                        let errorHtml = '<span class="backend-error text-danger">' + messages[0] + '</span>';
                        $("[name='" + field + "']").after(errorHtml);
                    });
                } else if (response.status === 200) {
                    console.log(response);  // Log the entire response
                    toast(response.message, "success");  // Corrected from response.msg to response.message
                    $("#form-driver-vehicle-information").removeClass("was-invalid");

                    setTimeout(function () {
                        window.location.reload();  // Reload the page
                    }, 2000);
                } else {
                    toast("Something went wrong.", "error");
                }
            },
            error: function (error) {
                $("#update_vehicle_info_btn").prop("disabled", false);
                toast("Something went wrong.", "error");
            },
            complete: function () {
                $("#update_vehicle_info_btn").prop("disabled", false);
            }
        });
    }

    $('.deleteVehicleDocument').on('click', function () {
        const url = $(this).data("url");

        $.confirm({
            icon: "fa fa-warning",
            title: "Confirm Delete!",
            content: "Do you want to delete this vehicle document?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            '_token': csrfToken, // Include CSRF token
                        },
                        success: function (response) {
                            if (response.success) {
                                console.log('data');
                                toast("Vehicle document deleted successfully.", "success");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while deleting the vehicle  document.", "error");
                            }
                        },
                        error: function (xhr) {
                            // Generic error handler for any server-side errors
                            const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : "An error occurred while processing your request.";
                            toast(errorMessage, "error");
                        }
                    });
                },
                cancel: function () {
                    // Cancel button action (optional)
                }
            }
        });
    });

    // Select2 - Vehicle Type
    $('#driver_vehicle').select2({
        placeholder: 'Select Vehicle',
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
            url: baseUrl + "/driver/pending/fetch/vehicle",
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


    // Select2 - Servicable Zone
    $('#driver_serviceable_location').select2({
        placeholder: 'Select Servicable Location',
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
            url: baseUrl + "/driver/pending/fetch/servicable-zone",
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
	
	
	//admin verification details
    $("#admin_verify_info_btn").on("click", function (e) {
		
        e.preventDefault();
        var form = $('#form-driver-admin-verification');
        if (form.valid()) { // Check if the form is valid
            var formData = new FormData(form[0]);
            $.confirm({
                icon: "fa fa-warning",
                title: "Confirm Verification!",
                content: "Are you sure you want to verify the partner?",
                theme: "modern",
                draggable: false,
                type: "blue",
                typeAnimated: true,
                buttons: {
                    confirm: function () {
                        admin_verify(formData);
                    },
                    cancel: function () {
                        // Action on cancel, if needed
                    }
                }
            });
        }
    });
	
	
	
    function admin_verify(formData) {
        var formUrl = `${baseUrl}/driver/pending/admin-verify/${driverId}`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#admin_verify_info_btn").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".text-danger").remove();
                    $(".backend-error").remove();
                    $.each(response.errors, function (field, messages) {
                        let errorHtml = '<span class="backend-error text-danger">' + messages[0] + '</span>';
                        $("[name='" + field + "']").after(errorHtml);
                    });
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-driver-admin-verification").removeClass("was-invalid");
                        $(".backend-error").remove();
                    } else {
                        toast("Something went wrong.", "error");
                    }
                }
            },
            error: function (xhr) {
                $("#admin_verify_info_btn").removeAttr("disabled");
                const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : "An error occurred while processing your request.";
                toast(errorMessage, "error");
            },
            complete: function () {
                $("#admin_verify_info_btn").removeAttr("disabled");
            },
        });
    }
	
    
    //verify personal and document details
    $("#verify_btn").on("click", function (e) {
        e.preventDefault();
        var form = $('#form-driver-personal-information');
        if (form.valid()) { // Check if the form is valid
            var formData = new FormData(form[0]);
            $.confirm({
                icon: "fa fa-warning",
                title: "Confirm Verification!",
                content: "Are you sure you want to verify the personal details?",
                theme: "modern",
                draggable: false,
                type: "blue",
                typeAnimated: true,
                buttons: {
                    confirm: function () {
                        verify_personal_document_info(formData);
                    },
                    cancel: function () {
                        // Action on cancel, if needed
                    }
                }
            });
        }
    });

    function verify_personal_document_info(formData) {
        var formUrl = `${baseUrl}/driver/pending/verify/${driverId}`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#verify_btn").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".text-danger").remove();
                    $(".backend-error").remove();
                    $.each(response.errors, function (field, messages) {
                        let errorHtml = '<span class="backend-error text-danger">' + messages[0] + '</span>';
                        $("[name='" + field + "']").after(errorHtml);
                    });
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-driver-personal-information").removeClass("was-invalid");
                        $(".backend-error").remove();
                    } else {
                        toast("Something went wrong.", "error");
                    }
                }
            },
            error: function (xhr) {
                $("#verify_btn").removeAttr("disabled");
                const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : "An error occurred while processing your request.";
                toast(errorMessage, "error");
            },
            complete: function () {
                $("#verify_btn").removeAttr("disabled");
            },
        });
    }

    //verify vehicle information
    $("#verify_vehicle_info_btn").on("click", function (e) {
        e.preventDefault();
        var form = $('#form-driver-vehicle-information');
        if (form.valid()) { // Check if the form is valid
            var formData = new FormData(form[0]);
            $.confirm({
                icon: "fa fa-warning",
                title: "Confirm Verification!",
                content: "Are you sure you want to verify the vehicle details?",
                theme: "modern",
                draggable: false,
                type: "blue",
                typeAnimated: true,
                buttons: {
                    confirm: function () {
                        verify_vehicle_document_info(formData);
                    },
                    cancel: function () {
                        // Action on cancel, if needed
                    }
                }
            });
        }
    });

    function verify_vehicle_document_info(formData) {
        var formUrl = `${baseUrl}/driver/pending/vehicle-information/verify/${driverId}`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#verify_vehicle_info_btn").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".text-danger").remove();
                    $(".backend-error").remove();
                    $.each(response.errors, function (field, messages) {
                        let errorHtml = '<span class="backend-error text-danger">' + messages[0] + '</span>';
                        $("[name='" + field + "']").after(errorHtml);
                    });
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-driver-vehicle-information").removeClass("was-invalid");
                        $(".backend-error").remove();
                    } else {
                        toast("Something went wrong.", "error");
                    }
                }
            },
            error: function (xhr) {
                $("#verify_vehicle_info_btn").removeAttr("disabled");
                const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : "An error occurred while processing your request.";
                toast(errorMessage, "error");
            },
            complete: function () {
                $("#verify_vehicle_info_btn").removeAttr("disabled");
            },
        });
    }
	
	
	$('#admin_verification_status').select2({
        placeholder: "Select Status",
        allowClear: true,
    }).on('select2:select', function (e) {
        handleVehicleVerificationStatusChange(e.params.data.id); // Pass selected value
    }).on('select2:clear', function () {
        handleVehicleVerificationStatusChange(null); // Handle clear action
    });

    function handleVehicleVerificationStatusChange(verificationStatus) {
        if (verificationStatus === 'reject') {
            // Show asterisk if 'reject' is selected
            $('.admin_verification_reason_required').removeClass('d-none');
        } else {
            // Remove asterisk if 'approve' is selected or when cleared
            $('.admin_verification_reason_required').addClass('d-none');
        }
    }
	

    $('#vehicle_verification_status').select2({
        placeholder: "Select Status",
        allowClear: true,
    }).on('select2:select', function (e) {
        handleVehicleVerificationStatusChange(e.params.data.id); // Pass selected value
    }).on('select2:clear', function () {
        handleVehicleVerificationStatusChange(null); // Handle clear action
    });

    function handleVehicleVerificationStatusChange(verificationStatus) {
        if (verificationStatus === 'reject') {
            // Show asterisk if 'reject' is selected
            $('.vehicle_verification_reason_required').removeClass('d-none');
        } else {
            // Remove asterisk if 'approve' is selected or when cleared
            $('.vehicle_verification_reason_required').addClass('d-none');
        }
    }

    //training Video verification 

    $('#training_video_verification_status').select2({
        placeholder: "Select Status",
        allowClear: true,
    }).on('select2:select', function (e) {
        handleTrainingVideoVerificationStatusChange(e.params.data.id); // Pass selected value
    }).on('select2:clear', function () {
        handleTrainingVideoVerificationStatusChange(null); // Handle clear action
    });

    function handleTrainingVideoVerificationStatusChange(verificationStatus) {
        if (verificationStatus === 'reject') {
            // Show asterisk if 'reject' is selected
            $('.training_video_verification_reason_required').removeClass('d-none');
        } else {
            // Remove asterisk if 'approve' is selected or when cleared
            $('.training_video_verification_reason_required').addClass('d-none');
        }
    }
	
	
	

    //validation for training video
    $('#form-driver-training-video-information').validate({
        rules: {
            training_video_verification_status: { required: true },
            training_video_verification_reason: {
                required: function () {
                    return $('#training_video_verification_status').val() === 'reject';
                }
            },
        },
        messages: {
            training_video_verification_status: { required: "Please select a verification status." },
            training_video_verification_reason: { required: "Please provide a reason for rejection." },
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('text-danger');
            if (element.hasClass('select2')) {
                error.insertAfter(element.next('.select2-container'));
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            $('backend-error').remove();
            form.submit(); // This will submit the form if you decide to keep this.
        }
    });

    $("#verify_training_video_info_btn").on("click", function (e) {
        e.preventDefault();
        var form = $('#form-driver-training-video-information');
        if (form.valid()) { // Check if the form is valid
            var formData = new FormData(form[0]);
            $.confirm({
                icon: "fa fa-warning",
                title: "Confirm Verification!",
                content: "Are you sure you want to verify the training video?",
                theme: "modern",
                draggable: false,
                type: "blue",
                typeAnimated: true,
                buttons: {
                    confirm: function () {
                        verify_training_video_info(formData);
                    },
                    cancel: function () {
                        // Action on cancel, if needed
                    }
                }
            });
        }
    });

    function verify_training_video_info(formData) {

        var formUrl = `${baseUrl}/driver/pending/training-video-verify/${driverId}`;
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#verify_training_video_info_btn").prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".text-danger").remove();
                    $(".backend-error").remove();
                    $.each(response.errors, function (field, messages) {
                        let errorHtml = '<span class="backend-error text-danger">' + messages[0] + '</span>';
                        $("[name='" + field + "']").after(errorHtml);
                    });
                } else {
                    if (response.status === 200) {
                        toast(response.message, "success");
                        $("#form-driver-training-video-information").removeClass("was-invalid");
                        $(".backend-error").remove();
                    } else {
                        toast("Something went wrong.", "error");
                    }
                }
            },
            error: function (xhr) {
                $("#verify_training_video_info_btn").removeAttr("disabled");
                const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : "An error occurred while processing your request.";
                toast(errorMessage, "error");
            },
            complete: function () {
                $("#verify_training_video_info_btn").removeAttr("disabled");
            },
        });
    }

    $('.deleteVehiclePhoto').on('click', function () {
        const url = $(this).data("url");

        $.confirm({
            icon: "fa fa-warning",
            title: "Confirm Delete!",
            content: "Do you want to delete this vehicle photo?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            '_token': csrfToken, // Include CSRF token
                        },
                        success: function (response) {
                            if (response.success) {
                                console.log('data');
                                toast("Vehicle photo deleted successfully.", "success");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while deleting the vehicle  document.", "error");
                            }
                        },
                        error: function (xhr) {
                            // Generic error handler for any server-side errors
                            const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : "An error occurred while processing your request.";
                            toast(errorMessage, "error");
                        }
                    });
                },
                cancel: function () {
                    // Cancel button action (optional)
                }
            }
        });
    });

});
