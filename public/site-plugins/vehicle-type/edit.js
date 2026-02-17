$(document).ready(function () {
    $.validator.addMethod("alphanumeric", function (value, element) {
        return this.optional(element) || /^[A-Za-z0-9\s]+$/.test(value);
    }, "Please enter only alphanumeric characters and spaces.");

    $('#form-edit-vehicle-type').validate({
        ignore: "",
        rules: {
            vehicle_type: {
                required: true,
                alphanumeric: true
            },
        },
        messages: {
            vehicle_type: {
                required: "The vehicle type field is required.",
                alphanumeric: "The vehicle type must be alphanumeric."
            },
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        },
        submitHandler: function (form) {
            form.submit();
        }
    });
	
	$('#vehicle_type').on('input', function () {
        $('#vehicle_type').next('span.backend-error').text('');
    });
});