$(function () {
    // Initialize Flatpickr for 'from_date'
	$('#from_date').flatpickr({
		dateFormat: "d-m-Y",
		onChange: function(selectedDates, dateStr, instance) {
			// When 'from_date' changes, set 'minDate' for 'to_date'
			let fromDate = selectedDates[0];
			$('#to_date').flatpickr({
				dateFormat: "d-m-Y",
				minDate: fromDate,  // Ensure to_date is always after from_date
			});
		}
	});

	// Initialize Flatpickr for 'to_date'
	$('#to_date').flatpickr({
		dateFormat: "d-m-Y",
		minDate: $('#from_date').val() ? new Date($('#from_date').val()) : null, // Check if from_date has value
	});
	
    // Searching user
    $('#search_filter').on('click', function (e) {
        var driver_id = $("#driver_name").val();
        var customer_id = $("#customer_name").val();
        var vehicle_id = $("#vehicle").val();
        var coupon_id = $("#coupon_code").val();
        var goods_type_id = $("#goods_type").val();
        var unique_id = $("#unique_id").val();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();
        getDataTable(driver_id, customer_id, vehicle_id, coupon_id, goods_type_id, unique_id, from_date, to_date);
    });

    // Clear button
    $("#reset_filter").click(function () {
        window.location.reload();
    });

    // Excel download
    $("#btnExcelDownload").on("click", function (e) {
        var driver_id = $("#driver_name").val();
        var customer_id = $("#customer_name").val();
        var vehicle_id = $("#vehicle").val();
        var coupon_id = $("#coupon_code").val();
        var goods_type_id = $("#goods_type").val();
        var unique_id = $("#unique_id").val();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();

        $.ajax({
            type: "get",
            url: baseUrl + "/cancel-trips/exceldownload",
            data: {
                driver_id: driver_id,
                customer_id: customer_id,
                vehicle_id: vehicle_id,
                coupon_id: coupon_id,
                goods_type_id: goods_type_id,
                unique_id: unique_id,
                from_date: from_date,
                to_date: to_date
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (response) {
                var blob = new Blob([response], { type: 'text/csv' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'cancel-trips.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function () {
                alert("An error occurred while downloading the CSV file.");
            }
        });
    });

    // PDF download
    $("#btnPdfDownload").on("click", function (e) {
        var driver_id = $("#driver_name").val();
        var customer_id = $("#customer_name").val();
        var vehicle_id = $("#vehicle").val();
        var coupon_id = $("#coupon_code").val();
        var goods_type_id = $("#goods_type").val();
        var unique_id = $("#unique_id").val();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();

        $.ajax({
            type: "get",
            url: baseUrl + "/cancel-trips/pdfdownload",
            data: {
                driver_id: driver_id,
                customer_id: customer_id,
                vehicle_id: vehicle_id,
                coupon_id: coupon_id,
                goods_type_id: goods_type_id,
                unique_id: unique_id,
                from_date: from_date,
                to_date: to_date
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'cancel-trips.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function () {
                alert("An error occurred while downloading the PDF file.");
            }
        });
    });

    // Select2 - customer name
    $('#customer_name').select2({
        placeholder: 'Select Customer',
        allowClear: true,
        minimumInputLength: 1,
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';
            },
            noResults: function () {
                return 'No data found';
            }
        },
        ajax: {
            url: baseUrl + "/cancel-trips/fetch/customer",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (response) {
                return {
                    results: response,
                };
            },
            cache: true,
        }
    });

    // Select2 - Driver Name
    $('#driver_name').select2({
        placeholder: 'Select Driver',
        allowClear: true,
        minimumInputLength: 1,
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';
            },
            noResults: function () {
                return 'No data found';
            }
        },
        ajax: {
            url: baseUrl + "/cancel-trips/fetch/driver",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (response) {
                return {
                    results: response,
                };
            },
            cache: true,
        }
    });

    // Select2 - Vehicle
    $('#vehicle').select2({
        placeholder: 'Select Vehicle',
        allowClear: true,
        minimumInputLength: 1,
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';
            },
            noResults: function () {
                return 'No data found';
            }
        },
        ajax: {
            url: baseUrl + "/cancel-trips/fetch/vehicle",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (response) {
                return {
                    results: response,
                };
            },
            cache: true,
        }
    });

    // Select2 - Coupon Code
    $('#coupon_code').select2({
        placeholder: 'Select Coupon',
        allowClear: true,
        minimumInputLength: 1,
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';
            },
            noResults: function () {
                return 'No data found';
            }
        },
        ajax: {
            url: baseUrl + "/cancel-trips/fetch/coupon",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (response) {
                return {
                    results: response,
                };
            },
            cache: true,
        }
    });

    // Select2 - Goods Type
    $('#goods_type').select2({
        placeholder: 'Select Goods Type',
        allowClear: true,
        minimumInputLength: 1,
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';
            },
            noResults: function () {
                return 'No data found';
            }
        },
        ajax: {
            url: baseUrl + "/cancel-trips/fetch/goods",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (response) {
                return {
                    results: response,
                };
            },
            cache: true,
        }
    });

    // Select2 - Unique ID
    $('#unique_id').select2({
        placeholder: 'Select Unique ID',
        allowClear: true,
        minimumInputLength: 1,
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...';
            },
            noResults: function () {
                return 'No data found';
            }
        },
        ajax: {
            url: baseUrl + "/cancel-trips/fetch/trip",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (response) {
                return {
                    results: response,
                };
            },
            cache: true,
        }
    });

    // Call the getDataTable function with default parameters
    getDataTable("", "");

});

// Trip list
function getDataTable(driver_id, customer_id, vehicle_id, coupon_id, goods_type_id, unique_id, from_date, to_date) {
    $.fn.DataTable.ext.errMode = "none";

    if ($.fn.DataTable.isDataTable("#dt-trip")) {
        $("#dt-trip").DataTable().clear().destroy();
    }

    $("#dt-trip").DataTable({
        stateSave: true,
        lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: true,
        ajax: {
            url: baseUrl + "/cancel-trips/list",
            type: "GET",
            data: function (d) {
                d.driver_id = driver_id;
                d.customer_id = customer_id;
                d.vehicle_id = vehicle_id;
                d.coupon_id = coupon_id;
                d.goods_type_id = goods_type_id;
                d.unique_id = unique_id;
                d.from_date = from_date;
                d.to_date = to_date;

                // Pass DataTable search value manually to backend
                d.search = { value: d.search?.value || "" };
            },
            complete: function (response) {
                // Optional callback after data is loaded
            },
        }
    });
}
