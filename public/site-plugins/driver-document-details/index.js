$(document).ready(function () {
    getDataTable();
})
function getDataTable() {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dt-driver-document-details")) {
        $("#dt-driver-document-details").DataTable().clear().destroy();
    }
    var dataTable = $("#dt-driver-document-details").DataTable({
        stateSave: false,
        lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: true,
        ajax: {
            url: baseUrl + "/driver-document-details/list",
            type: "GET",
            data: {},
            complete: function (response) {
                operations();
            },
        },
    });
}

function accountActivateOrSuspend(id, type) {
    let typeText = type === 'suspend' ? 'Suspend' : 'Activate';
    $.confirm({
        icon: "fa fa-warning",
        title: "Confirm " + typeText + "!",
        content: "Do you want to " + type + " this Driver?",
        theme: "modern",
        draggable: false,
        type: "red",
        typeAnimated: true,
        buttons: {
            confirm: function () {
                $.ajax({
                    url: baseUrl + "/driver/" + id + "/" + type,
                    type: "DELETE",
                    data: {
                        '_token': csrfToken, // Include CSRF token
                    },
                    success: function (response) {
                        if (response.success) {
                            toast("Driver " + type + " successfully.", "success");
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            toast(response.error || "An error occurred while " + type + " the Driver.", "error");
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
}

function operations() {
    // Delete 
    $("a.btn-delete").on("click", function () {
        const id = $(this).data("id");

        $.confirm({
            icon: "fa fa-warning",
            title: "Confirm Delete!",
            content: "Do you want to delete this Driver?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: baseUrl + "/driver/" + id,
                        type: "DELETE",
                        data: {
                            '_token': csrfToken, // Include CSRF token
                        },
                        success: function (response) {
                            if (response.success) {
                                toast("Driver deleted successfully.", "success");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while deleting the Driver.", "error");
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

    // Suspend  
    $("a.btn-suspend").on("click", function () {
        const id = $(this).data("id");
        const type = "suspend";
        accountActivateOrSuspend(id, type);
    });

    // Activate  
    $("a.btn-activate").on("click", function () {
        const id = $(this).data("id");
        const type = "activate";
        accountActivateOrSuspend(id, type);
    });
}