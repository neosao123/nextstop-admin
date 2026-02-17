$(document).ready(function () {
    //form validation
	
	$.validator.addMethod("alphaspace", function (value, element) {
    return this.optional(element) || /^[A-Za-z\s]+$/.test(value);
	}, "Please enter only alphabetic characters and spaces.");

	$.validator.addMethod("validemail", function (value, element) {
		return this.optional(element) || /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
	}, "Please enter a valid email address.");

	$.validator.addMethod("validpassword", function (value, element) {
		return this.optional(element) || /^\S+$/.test(value);
	}, "Passwords cannot contain spaces. Please enter a valid password.");

	$('#form-add-user').validate({
		ignore: "",
		rules: {
			role: {
				required: true,
			},
			first_name: {
				required: true,
				minlength: 2,
				maxlength: 150,
				alphaspace: true
			},
			last_name: {
				required: true,
				minlength: 2,
				maxlength: 150,
				alphaspace: true
			},
			email: {
				required: true,
				email: true,
				minlength: 2,
				maxlength: 150,
				validemail: true
			},
			phone_number: {
				required: true,
				number: true,
				minlength: 10
			},
			password: {
				required: false,
				minlength: 6,
				maxlength: 20,
				validpassword: true
			},
			password_confirmation: {
				required: false,
				minlength: 6,
				maxlength: 20,
				equalTo: "#password",
				validpassword: true
			}
		},
		messages: {
			role: {
				required: "The Role is required",
			},
			first_name: {
				required: "The First name is required.",
				minlength: "The First name must be at least 2 characters long.",
				maxlength: "The First name cannot exceed 150 characters.",
				alphaspace: "The First name must contain only alphabetic characters and spaces."
			},
			last_name: {
				required: "The Last name is required.",
				minlength: "The Last name must be at least 2 characters long.",
				maxlength: "The Last name cannot exceed 150 characters.",
				alphaspace: "The Last name must contain only alphabetic characters and spaces."
			},
			email: {
				required: "The Email is required.",
				email: "Please enter a valid email address.",
				minlength: "The Email must be at least 2 characters long.",
				maxlength: "The Email cannot exceed 150 characters.",
				validemail: "Please enter a valid email address."
			},
			phone_number: {
				required: "The Phone number is required.",
				number: "Please enter a valid number.",
				minlength: "The Phone number must be 10 digits."
			},
			password: {
				required: "The Password is required.",
				minlength: "The Password must be at least 6 characters long.",
				maxlength: "The password must not exceed 20 characters.",
				validpassword: "Passwords cannot contain spaces. Please enter a valid password."
			},
			password_confirmation: {
				required: "The Confirm password is required.",
				minlength: "The Confirm password must be at least 6 characters long.",
				equalTo: "Password does not match the confirm password.",
				maxlength: "The Confirm password must not exceed 20 characters.",
				validpassword: "Passwords cannot contain spaces. Please enter a valid password."
			}
		},
		errorPlacement: function (error, element) {
			if (element.hasClass('select2')) {
				error.insertAfter(element.next('.select2-container')); // For Select2
			} else {
				error.insertAfter(element); // For other inputs
			}
		},
		submitHandler: function (form) {
			form.submit();
		}
	});
	
	$('#first_name').on('input', function () {
        $('#first_name').next('span.backend-error').text('');
    });
	
	$('#last_name').on('input', function () {
        $('#last_name').next('span.backend-error').text('');
    });
	
	$('#phone_number').on('input', function () {
        $('#phone_number').next('span.backend-error').text('');
    });
	
	$('#email').on('input', function () {
        $('#email').next('span.backend-error').text('');
    });
	
	$('#password').on('input', function () {
        $('#password').next('span.backend-error').text('');
    });
	
	$('#password_confirmation').on('input', function () {
        $('#password_confirmation').next('span.backend-error').text('');
    });
	
	// Select2 - Role 
    $('#role').select2({
        placeholder: 'Select Role',
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
            url: baseUrl + "/users/fetch/role",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (response) {
                return {
                    results: response,
                };
            },
            cache: true,
        }
    });
	
	//file validation
	$("#file").on("change", function () {
        var filePath = $(this).val();
        var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;
        if (!allowedExtensions.exec(filePath)) {
            $(this).val(null);
            return false;
        } else {
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function (event) {
                    $("#showImage").attr("src", event.target.result);
                };
                reader.readAsDataURL(file);
            }
        }
    });
});
