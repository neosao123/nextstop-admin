$(function () {
	
	$.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    }); 
	
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
	
	
    // Search button functionality
    $('#search_filter').on('click', function () {
        var driver = $("#driver").val();
        var type= $("#type").val();
		var status= $("#status").val();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();
        getDataTable(driver,from_date,to_date,type,status);
    });

    // Clear button functionality
    $("#reset_filter").click(function () {
        window.location.reload();
    });

   
    // Initialize Select2 for Driver
    $('#driver').select2({
        placeholder: 'Select Partner',
        allowClear: true,
        minimumInputLength: 1,
        language: {
            inputTooShort: () => 'Please enter 1 or more characters',
            searching: () => 'Searching...',
            noResults: () => 'No data found'
        },
        ajax: {
            url: baseUrl + "/driver-payment-history/fetch/driver",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: params => ({ search: params.term }),
            processResults: response => ({ results: response }),
            cache: true
        }
    });
	
	
	$("#btnExcelDownload").on("click", function (e) {
	    var driver = $("#driver").val();
        var type= $("#type").val();
		var status= $("#status").val();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();
		$.ajax({
			type: "get",
			url: baseUrl + "/driver-payment-history/exceldownload",
			data: {
				driver: driver,
				type:type,
				status: status,
				from_date:from_date,
				to_date:to_date
			},
			xhrFields: {
				responseType: 'blob'
			},
			success: function (response) {
				var blob = new Blob([response], { type: 'text/csv' });
				var link = document.createElement('a');
				link.href = window.URL.createObjectURL(blob);
				link.download = 'PartnerPaymentHistory.csv';
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			},
			error: function () {
				alert("An error occurred while downloading the CSV file.");
			}
		});
	});


    $("#btnPdfDownload").on("click", function (e) {
        var driver = $("#driver").val();
        var type= $("#type").val();
		var status= $("#status").val();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();
		
        $.ajax({
            type: "get",
            url: baseUrl + "/driver-payment-history/pdfdownload",
            data: {
                driver: driver,
				type:type,
				status: status,
				from_date:from_date,
				to_date:to_date
            },
            xhrFields: {
                responseType: 'blob' // This is important to handle the response as a blob
            },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'PartnerPaymentHistory.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
        });
    });
	
	
	$("#btnPayoutExcelDownload").on("click", function (e) {
	    var driver = $("#driver").val();
        var type= $("#type").val();
		var status= $("#status").val();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();
		$.ajax({
			type: "get",
			url: baseUrl + "/driver-payment-history/payout/exceldownload",
			data: {
				driver: driver,
				type:type,
				status: status,
				from_date:from_date,
				to_date:to_date
			},
			xhrFields: {
				responseType: 'blob'
			},
			success: function (response) {
				var blob = new Blob([response], { type: 'text/csv' });
				var link = document.createElement('a');
				link.href = window.URL.createObjectURL(blob);
				link.download = 'Payout.csv';
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			},
			error: function () {
				alert("An error occurred while downloading the CSV file.");
			}
		});
	});
	
	
	 $("#btnPayoutPdfDownload").on("click", function (e) {
        var driver = $("#driver").val();
        var type= $("#type").val();
		var status= $("#status").val();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();
		
        $.ajax({
            type: "get",
            url: baseUrl + "/driver-payment-history/payout/pdfdownload",
            data: {
                driver: driver,
				type:type,
				status: status,
				from_date:from_date,
				to_date:to_date
            },
            xhrFields: {
                responseType: 'blob' // This is important to handle the response as a blob
            },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Payout.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
        });
    });
	

	

    // Initialize data table
    getDataTable();

    // Operations (placeholder for any future logic)
    function operations() {
		
	
	}

    // Function to load driver transaction list into DataTable
    function getDataTable(driver,from_date,to_date,type,status) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dt-transaction")) {
            $("#dt-transaction").DataTable().clear().destroy();
        }

        $("#dt-transaction").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/driver-payment-history/list",
                type: "GET",
                data: { driver:driver,
                        from_date:from_date,
                        to_date:to_date,
                        type:type,
						status:status
                      },
                complete: () => operations()
            }
        });
    }
});
