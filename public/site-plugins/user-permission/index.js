$(function() {
        'use-strict';
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        $(document).on('change', 'input[type="checkbox"]', function() {
            var checkboxName = $(this).attr('name');
            var dataPermission = $(this).data('permission');
            dataPermission = dataPermission.split("|")
            console.log(dataPermission);
            let mode = "set";
            if ($(this).is(':checked')) {
                mode = "set"; 
            } else {
                mode = "revoke";
            }

            $.ajax({
                type: "get",
                url: `${baseUrl}/user/${userId}/set-permission`,
                data: {
                    'mode': mode,
                    'userId': userId,
                    'permissionId': dataPermission[0]
                },
                dataType: "JSON",
                success: function(response) {
                    if (response.status === 200) {
                        toast(response.message, "success", '2', 'right', 'bottom');
                    }
                    if (response.status === 400) {
                        toast(response.message, "error", '2', 'right', 'bottom');
                        // $(`chk-${response.data.permissionId}`).prop('checked', true);
                    }
                },
                error: function(ex) {
                    console.log(ex)
                }
            });
        });
    });