$(document).ready(function () {
    getDataTable();


    // Select2 - Vehicle 
    $('#search_driver_vehicle').select2({
        placeholder: 'Select Vehicle',
        allowClear: true,
        minimumInputLength: 1,  // Prevents the request until 1 or more characters are entered
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';  // You can customize this message
            },
            noResults: function () {
                return 'No data found';  // No data found message
            }
        },
        ajax: {
            url: baseUrl + "/driver/verified/fetch/vehicle",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term // Only send the term if it's longer than 1 character
                };
            },
            processResults: function (response) {
                return {
                    results: response,  // Your response should contain the expected format
                };
            },
            cache: true,
        }
    });

    // Select2 - Servicable Zone
    $('#search_driver_serviceable_location').select2({
        placeholder: 'Select Servicable Location',
        allowClear: true,
        minimumInputLength: 1,  // Prevents the request until 1 or more characters are entered
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';  // You can customize this message
            },
            noResults: function () {
                return 'No data found';  // No data found message
            }
        },
        ajax: {
            url: baseUrl + "/driver/verified/fetch/servicable-zone",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term // Only send the term if it's longer than 1 character
                };
            },
            processResults: function (response) {
                return {
                    results: response,  // Your response should contain the expected format
                };
            },
            cache: true,
        }
    });

    // Select2 - Account Status
    $('#search_account_status').select2({
        placeholder: 'Select Account Status',
        allowClear: true,
    });

    $('#reset_filter').on('click', function () {
        window.location.reload();
    });

    $('#search_filter').on('click', function () {
        var search_name = $('#search_name').val() ?? '';
        var search_driver_vehicle = $('#search_driver_vehicle').val() ?? '';
        var search_driver_serviceable_location = $('#search_driver_serviceable_location').val() ?? '';
        var search_account_status = $('#search_account_status').val() ?? '';

        getDataTable(
            search_name,
            search_driver_vehicle,
            search_driver_serviceable_location,
            search_account_status,
        )

    });


    $("#exportExcelBtn").on("click", function (e) {
        var search_name = $('#search_name').val() ?? '';
        var search_driver_vehicle = $('#search_driver_vehicle').val() ?? '';
        var search_driver_serviceable_location = $('#search_driver_serviceable_location').val() ?? '';
        var search_account_status = $('#search_account_status').val() ?? '';
        var search_verification_status_type = $('#search_verification_status_type').val() ?? '';
        var search_verification_status = $('#search_verification_status').val() ?? '';
        $.ajax({
            type: "GET",
            url: baseUrl + '/driver/verified/excel-export',

            data: {
                export: 1,
                _token: csrfToken,
                search_name: search_name ?? '',
                search_driver_vehicle: search_driver_vehicle ?? '',
                search_driver_serviceable_location: search_driver_serviceable_location ?? '',
                search_account_status: search_account_status ?? '',
                search_verification_status_type: search_verification_status_type ?? [],
                search_verification_status: search_verification_status ?? '',
            },
            success: function (response, status, xhr) {
                // Step 1: Extract the filename from the Content-Disposition header
                var disposition = xhr.getResponseHeader('Content-Disposition');
                var filename = 'Verified-Partner' + '.csv';  // Default filename in case extraction fails

                // Step 2: Create a Blob object for the response data
                var blob = new Blob([response], { type: 'text/csv' });  // Create a Blob from the CSV content

                // Step 3: Create a download link programmatically
                var link = document.createElement('a');  // Create an anchor element
                link.href = window.URL.createObjectURL(blob);  // Create a temporary URL for the Blob
                link.download = filename;  // Set the filename for the downloaded file

                // Step 4: Trigger the download
                document.body.appendChild(link);  // Append the link to the document body (required for the click to work)
                link.click();  // Programmatically click the link to start the download
                document.body.removeChild(link);  // Clean up by removing the link
            },


        });
    });

    $("#exportPdfBtn").on("click", function (e) {
        var search_name = $('#search_name').val() ?? '';
        var search_driver_vehicle = $('#search_driver_vehicle').val() ?? '';
        var search_driver_serviceable_location = $('#search_driver_serviceable_location').val() ?? '';
        var search_account_status = $('#search_account_status').val() ?? '';
        var search_verification_status_type = $('#search_verification_status_type').val() ?? '';
        var search_verification_status = $('#search_verification_status').val() ?? '';
        $.ajax({
            type: "get",
            url: baseUrl + "/driver/verified/pdf-export",
            data: {
                export: 1,
                _token: csrfToken,
                search_name: search_name ?? '',
                search_driver_vehicle: search_driver_vehicle ?? '',
                search_driver_serviceable_location: search_driver_serviceable_location ?? '',
                search_account_status: search_account_status ?? '',
                search_verification_status_type: search_verification_status_type ?? [],
                search_verification_status: search_verification_status ?? '',
            },
            xhrFields: {
                responseType: 'blob' // This is important to handle the response as a blob
            },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Verified-Partner.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
        });
    });

})
function getDataTable(search_name, search_driver_vehicle, search_driver_serviceable_location, search_account_status) {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dt-verified-driver")) {
        $("#dt-verified-driver").DataTable().clear().destroy();
    }
    var dataTable = $("#dt-verified-driver").DataTable({
        stateSave: false,
        lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: true,
        ajax: {
            url: baseUrl + "/driver/verified/list",
            type: "GET",
            data: {
                search_name: search_name ?? '',
                search_driver_vehicle: search_driver_vehicle ?? '',
                search_driver_serviceable_location: search_driver_serviceable_location ?? '',
                search_account_status: search_account_status ?? '',
            },
            complete: function (response) {
                operations();
            },
        },
    });
}

function accountBlockOrUnblock(id, type) {
    let typeText = type === 'block' ? 'Blocked' : 'Unblocked';
    $.confirm({
        icon: "fa fa-warning",
        title: "Confirm " + typeText + "!",
        content: "Do you want to " + type + " this partner?",
        theme: "modern",
        draggable: false,
        type: "red",
        typeAnimated: true,
        buttons: {
            confirm: function () {
                $.ajax({
                    url: baseUrl + "/driver/verified/" + id + "/" + type,
                    type: "DELETE",
                    data: {
                        '_token': csrfToken, // Include CSRF token
                    },
                    success: function (response) {
                        if (response.success) {
                            toast("Partner " + typeText + " successfully.", "success");
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
            content: "Do you want to delete this partner?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: baseUrl + "/driver/verified/" + id,
                        type: "DELETE",
                        data: {
                            '_token': csrfToken, // Include CSRF token
                        },
                        success: function (response) {
                            if (response.success) {
                                toast("partner deleted successfully.", "success");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while deleting the partner.", "error");
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

    // Block  
    $("a.btn-block").on("click", function () {
        const id = $(this).data("id");
        const type = "block";
        accountBlockOrUnblock(id, type);
    });

    // Unblock  
    $("a.btn-unblock").on("click", function () {
        const id = $(this).data("id");
        const type = "unblock";
        accountBlockOrUnblock(id, type);
    });
}