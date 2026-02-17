$(document).ready(function () {
    //form validation
	
	$.validator.addMethod("alphaspace", function (value, element) {
    return this.optional(element) || /^[A-Za-z\s]+$/.test(value);
	}, "Please enter only alphabetic characters and spaces.");

	$.validator.addMethod("validemail", function (value, element) {
		return this.optional(element) || /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
	}, "Please enter a valid email address.");

	$('#form-edit-customer').validate({
		ignore: "",
		rules: {
			role: {
				required: true,
			},
			customer_first_name: {
				required: true,
				minlength: 2,
				maxlength: 150,
				alphaspace: true
			},
			customer_last_name: {
				required: true,
				minlength: 2,
				maxlength: 150,
				alphaspace: true
			},
			customer_email: {
				required: true,
				email: true,
				minlength: 2,
				maxlength: 150,
				validemail: true
			},
			customer_phone: {
				required: true,
				number: true,
				minlength: 10
			}
		},
		messages: {
			customer_first_name: {
				required: "The Customer first name is required.",
				minlength: "The Customer first name must be at least 2 characters long.",
				maxlength: "The Customer first name cannot exceed 150 characters.",
				alphaspace: "The Customer first name must contain only alphabetic characters and spaces."
			},
			customer_last_name: {
				required: "The Customer last name is required.",
				minlength: "The Customer last name must be at least 2 characters long.",
				maxlength: "The Customer last name cannot exceed 150 characters.",
				alphaspace: "The Customer last name must contain only alphabetic characters and spaces."
			},
			customer_email: {
				required: "The Customer email is required.",
				email: "Please enter a valid email address.",
				minlength: "The Customer email must be at least 2 characters long.",
				maxlength: "The Customer email cannot exceed 150 characters.",
				validemail: "Please enter a valid email address."
			},
			customer_phone: {
				required: "The Customer phone number is required.",
				number: "Please enter a valid number.",
				minlength: "The Customer phone number must be 10 digits."
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
	
	$('#customer_first_name').on('input', function () {
        $('#customer_first_name').next('span.backend-error').text('');
    });
	
	$('#customer_last_name').on('input', function () {
        $('#customer_last_name').next('span.backend-error').text('');
    });
	
	$('#customer_phone').on('input', function () {
        $('#customer_phone').next('span.backend-error').text('');
    });
	
	$('#customer_email').on('input', function () {
        $('#customer_email').next('span.backend-error').text('');
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
