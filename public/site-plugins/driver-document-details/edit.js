$(document).ready(function () {

    // alert();
    $.validator.addMethod("regex", function (value, element, regex) {
        return this.optional(element) || regex.test(value);
    }, "Invalid format.");

    // Edit Driver - Form Validate
    $('#form-edit-driver').validate({
        rules: {
            porter_first_name: {
                required: true,
                minlength: 2,
                maxlength: 150,
                regex: /^[A-Za-z\s]+$/
            },
            porter_last_name: {
                required: true,
                minlength: 2,
                maxlength: 150,
                regex: /^[A-Za-z\s]+$/
            },
            porter_email: {
                required: false,
                email: true,
                minlength: 2,
                maxlength: 150,
                regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
            }
        },
        messages: {
            porter_first_name: {
                required: "Please enter the first name.",
                minlength: "First name must be at least 2 characters long.",
                maxlength: "First name cannot exceed 150 characters.",
                regex: "First name can contain only letters and spaces."
            },
            porter_last_name: {
                required: "Please enter the last name.",
                minlength: "Last name must be at least 2 characters long.",
                maxlength: "Last name cannot exceed 150 characters.",
                regex: "Last name can contain only letters and spaces."
            },
            porter_email: {
                email: "Please enter a valid email address.",
                minlength: "Email must be at least 2 characters long.",
                maxlength: "Email cannot exceed 150 characters.",
                regex: "Please enter a valid email format."
            }
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            // $('span.text-danger').text('');
            error.addClass('text-danger');
            error.insertAfter(element); // Inserts error message after the input field
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    $('#porter_email').on('input', function () {
        $('#porter_email').next('span.backend-error').text('');
    });

});
