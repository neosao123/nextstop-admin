$(document).ready(function () {
    getDataTable();
})
function getDataTable() {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dt-vehicle-type")) {
        $("#dt-vehicle-type").DataTable().clear().destroy();
    }
    var dataTable = $("#dt-vehicle-type").DataTable({
        stateSave: false,
        lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: true,
        ajax: {
            url: baseUrl + "/vehicle-type/list",
            type: "GET",
            data: {},
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
            content: "Do you want to delete this vehicle type?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: baseUrl + "/vehicle-type/" + id,
                        type: "DELETE",
                        data: {
                            '_token': csrfToken, // Include CSRF token
                        },
                        success: function (response) {
                            if (response.success) {
                                toast("Vehicle type deleted successfully.", "success");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while deleting the vehicle type.", "error");
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