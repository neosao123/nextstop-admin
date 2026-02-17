$(document).ready(function() {
            setTimeout(function() {
                $('.my-alert').remove()
            }, 5000);

            $("#password_confirmation").on("change", function() {
                var cpassword = $(this).val();
                var password = $('#password').val();
                if (cpassword != password) {
                    $("#altbx").text("Password does not match.");
                    setTimeout(() => {
                        $("#altbx").empty();
                    }, 5000);
                    $('#submit').prop("disabled", true);
                    return false;
                } else {
                    $('#submit').prop("disabled", false);
                }
            });

            $("#password").on("change", function() {
                var cpassword = $(this).val();
                var password = $('#password_confirmation').val();
                if (cpassword != password && password != '') {
                    $("#altbx").text("Password does not match.");
                    setTimeout(() => {
                        $("#altbx").empty();
                    }, 5000);
                    $('#submit').prop("disabled", true);
                    return false;
                } else {
                    $('#submit').prop("disabled", false);
                }
            });

        });