$(document).ready(function () {
    $('#form-edit-customer-reason').validate({
        ignore: "",
        rules: {
            reason: {
                required: true,
				minlength: 2,
                maxlength: 200
            },
        },
        messages: {
            reason: {
                required: "The reason field is required.",			
                minlength: "The First name must be at least 2 characters long.",
                maxlength: "The First name cannot exceed 200 characters."
            },
        },
        errorPlacement: function (error, element) {
			 error.addClass('text-danger');
            error.insertAfter(element);
        },
        submitHandler: function (form) {
            form.submit();
        }
    });
	
	$('#reason').on('input', function () {
        $('#reason').next('span.backend-error').text('');
    });
});