$(document).ready(function () {
    getDataTable();

    $('#search_coupon_type').select2({
        placeholder: 'Select Coupon Type',
        allowClear: true,
    })
    $('#search_status').select2({
        placeholder: 'Select Status',
        allowClear: true,
    })
	
	$('#from_date').flatpickr({
        dateFormat: "d-m-Y",
    });
	
	$('#to_date').flatpickr({
        dateFormat: "d-m-Y"
    });


    $('#search_filter').on('click', function () {
        var search_coupon_type = $('#search_coupon_type').val();
        var search_status = $('#search_status').val();
        var from_date=$("#from_date").val();
		var to_date=$("#to_date").val();
        getDataTable(search_coupon_type, search_status,from_date,to_date);
    });

    $('#reset_filter').on('click', function () {
        window.location.reload();
    });

})
function getDataTable(search_coupon_type, search_status,from_date,to_date) {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dt-coupon")) {
        $("#dt-coupon").DataTable().clear().destroy();
    }
    var dataTable = $("#dt-coupon").DataTable({
        stateSave: false,
        lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: true,
        ajax: {
            url: baseUrl + "/coupon/list",
            type: "GET",
            data: {
                search_coupon_type: search_coupon_type,
                search_status: search_status,
				from_date:from_date,
				to_date:to_date
            },
            complete: function (response) {
                operations();
            },
        },
    });
}
function operations() {
    // Delete Project
    $("a.btn-delete").on("click", function () {
        const id = $(this).data("id");

        $.confirm({
            icon: "fa fa-warning",
            title: "Confirm Delete!",
            content: "Do you want to delete this coupon?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: baseUrl + "/coupon/" + id,
                        type: "DELETE",
                        data: {
                            '_token': csrfToken, // Include CSRF token
                        },
                        success: function (response) {
                            if (response.success) {
                                console.log('data');
                                toast("Coupon deleted successfully.", "success");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while deleting the coupon.", "error");
                            }
                        },
                        error: function (xhr) {
                            // Generic error handler for any server-side errors
                            const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : "An error occurred while processing your request.";
                            toast(errorMessage, "error");
                        }
                    });
                },
                cancel: function () {
                    // Cancel button action (optional)
                }
            }
        });
    });
}