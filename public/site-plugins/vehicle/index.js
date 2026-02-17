$(document).ready(function () {
    getDataTable();

    // Select2 - Vehicle Type
    $('#search_vehicle_type').select2({
        placeholder: 'Select Type',
        allowClear: true,
        minimumInputLength: 1,
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
            url: baseUrl + "/vehicle/fetch/vehicle-type",
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
    })

    $('#search_filter').on('click', function () {
        var search_vehicle_type = $('#search_vehicle_type').val();
        var search_vehicle_max_load_capacity = $('#search_vehicle_max_load_capacity').val();
        var search_vehicle_per_km_delivery_charge = $('#search_vehicle_per_km_delivery_charge').val();

        getDataTable(search_vehicle_type, search_vehicle_max_load_capacity, search_vehicle_per_km_delivery_charge);
    });

    $('#reset_filter').on('click', function () {
        window.location.reload();
    });


    $("#exportExcelBtn").on("click", function (e) {
        var search_vehicle_type = $('#search_vehicle_type').val();
        var search_vehicle_max_load_capacity = $('#search_vehicle_max_load_capacity').val();
        var search_vehicle_per_km_delivery_charge = $('#search_vehicle_per_km_delivery_charge').val();
        $.ajax({
            type: "GET",
            url: baseUrl + '/vehicle/excel-export',

            data: {
                export: 1,
                _token: csrfToken,
                search_vehicle_type: search_vehicle_type,
                search_vehicle_max_load_capacity: search_vehicle_max_load_capacity,
                search_vehicle_per_km_delivery_charge: search_vehicle_per_km_delivery_charge
            },
            success: function (response, status, xhr) {
                // Step 1: Extract the filename from the Content-Disposition header
                var disposition = xhr.getResponseHeader('Content-Disposition');
                var filename = 'Vehicle' + '.csv';  // Default filename in case extraction fails

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
        var search_vehicle_type = $('#search_vehicle_type').val();
        var search_vehicle_max_load_capacity = $('#search_vehicle_max_load_capacity').val();
        var search_vehicle_per_km_delivery_charge = $('#search_vehicle_per_km_delivery_charge').val();
        $.ajax({
            type: "get",
            url: baseUrl + "/vehicle/pdf-export",
            data: {
                export: 1,
                _token: csrfToken,
                search_vehicle_type: search_vehicle_type,
                search_vehicle_max_load_capacity: search_vehicle_max_load_capacity,
                search_vehicle_per_km_delivery_charge: search_vehicle_per_km_delivery_charge
            },
            xhrFields: {
                responseType: 'blob' // This is important to handle the response as a blob
            },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Pending-driver.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
        });
    });
})
function getDataTable(search_vehicle_type, search_vehicle_max_load_capacity, search_vehicle_per_km_delivery_charge) {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dt-vehicle")) {
        $("#dt-vehicle").DataTable().clear().destroy();
    }
    var dataTable = $("#dt-vehicle").DataTable({
        stateSave: false,
        lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: true,
        ajax: {
            url: baseUrl + "/vehicle/list",
            type: "GET",
            data: {
                search_vehicle_type: search_vehicle_type,
                search_vehicle_max_load_capacity: search_vehicle_max_load_capacity,
                search_vehicle_per_km_delivery_charge: search_vehicle_per_km_delivery_charge
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
            content: "Do you want to delete this vehicle?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: baseUrl + "/vehicle/" + id,
                        type: "DELETE",
                        data: {
                            '_token': csrfToken, // Include CSRF token
                        },
                        success: function (response) {
                            if (response.success) {
                                console.log('data');
                                toast("Vehicle deleted successfully.", "success");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while deleting the vehicle.", "error");
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