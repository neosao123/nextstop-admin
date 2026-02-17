$(document).ready(function () {
    
	$('#form-add-user').validate({
        ignore: "",
        rules: {
            first_name: {
                required: true,
                minlength: 2,
                maxlength: 150
            },
            last_name: {
                required: true,
                minlength: 2,
                maxlength: 150
            },
            email: {
                required: true,
                email: true,
				minlength: 2,
				maxlength: 150
            },
            phone_number: {
                required: true,
                number: true,
                minlength: 10,
                maxlength: 12
            }
        },
        messages: {
            first_name: {
                required: "The First name is required.",
                minlength: "The First name must be at least 2 characters long.",
                maxlength: "The First name cannot exceed 150 characters."
            },
            last_name: {
                required: "The Last name is required.",
                minlength: "The Last name must be at least 2 characters long.",
                maxlength: "The Last name cannot exceed 150 characters."
            },
            email: {
                required: "The Email is required.",
                email: "Please enter a valid email address.",
			    minlength: "The Email must be at least 2 characters long.",
                maxlength: "The Email cannot exceed 150 characters."
            },
            phone_number: {
                required: "The Phone number is required.",
                number: "Please enter a valid number.",
                minlength: "The Phone number must be 10 digits.",
                maxlength: "The Phone number must be 12 digits."
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
