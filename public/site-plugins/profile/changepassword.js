$(document).ready(function () {
    //form validation
	
	$('#form-update-password').validate({
        ignore: "",
        rules: {
			old_password: {
                required: true,
            },
            new_password: {
                required: true,
                minlength: 6,
				maxlength:20
            },
            password_confirmation: {
                required: true,
                minlength: 6,
				maxlength:20,
                equalTo: "#new_password"
            }
        },
        messages: {
			old_password: {
                required: "The Old password is required.",
            },
            new_password: {
                required: "The New password is required.",
                minlength: "The New password must be at least 6 characters long.",
				maxlength:"The New password must not exceed 20 characters."
            },
            password_confirmation: {
                required: "The Confirm password is required.",
                minlength: "The Confirm password must be at least 6 characters long.",
                equalTo: "Password is not matched confirm password.",
				maxlength:"The Confirm password must not exceed 20 characters."
            }
        },
        errorPlacement: function (error, element) {
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
	
});