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
	
	// Initial customer value
    var customer = $("#customer").val();

    // Search button functionality
    $('#search_filter').on('click', function () {
        var customer = $("#customer").val();
        var trip = $("#trip").val();
        var type= $("#type").val();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();
        getDataTable(customer, trip,from_date,to_date,type);
    });

    // Clear button functionality
    $("#reset_filter").click(function () {
        window.location.reload();
    });

   
    // Initialize Select2 for Trip
    $('#trip').select2({
        placeholder: 'Select Trip',
        allowClear: true,
        minimumInputLength: 1,
        language: {
            inputTooShort: () => 'Please enter 1 or more characters',
            searching: () => 'Searching...',
            noResults: () => 'No data found'
        },
        ajax: {
            url: baseUrl + "/customers/fetch/trip",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: params => ({ search: params.term }),
            processResults: response => ({ results: response }),
            cache: true
        }
    });

    // Initialize data table
    getDataTable(customer, "", "");

    // Operations (placeholder for any future logic)
    function operations() {}

    // Function to load customer transaction list into DataTable
    function getDataTable(customer,trip,from_date,to_date,type) {
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
                url: baseUrl + "/customers/wallet-transaction/list",
                type: "GET",
                data: { customer:customer,
                        trip:trip,
                        from_date:from_date,
                        to_date:to_date,
                        type:type
                      },
                complete: () => operations()
            }
        });
    }
});
