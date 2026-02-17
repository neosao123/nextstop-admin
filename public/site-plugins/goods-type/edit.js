$(document).ready(function () {
    $.validator.addMethod("alphanumeric", function (value, element) {
        return this.optional(element) || /^[A-Za-z0-9\s\/\\|&\-_\,]+$/.test(value);
    }, "Please enter valid characters (letters, numbers, and allowed special characters: / \\ | & - _ , ).");


    $('#form-edit-goods-type').validate({
        ignore: "",
        rules: {
            goods_name: {
                required: true,
                minlength: 2,
                maxlength: 150,
                alphanumeric: true
            },
        },
        messages: {
            goods_name: {
                required: "The goods type name field is required.",
                alphanumeric: "The goods type name may only contain letters, numbers, spaces, and the following characters: / \\ | & - _ ,",
                minlength: "The goods type name must be at least 2 characters.",
                maxlength: "The goods type name may not be greater than 150 characters."
            },
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('text-danger');
            error.insertAfter(element);
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    $('#goods_name').on('input', function () {
        $('#goods_name').next('span.backend-error').text('');
    });
});