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
            url: baseUrl + "/driver-earning/fetch/driver",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: params => ({ search: params.term }),
            processResults: response => ({ results: response }),
            cache: true
        }
    });

    // Initialize data table
    getDataTable();

    // Operations (placeholder for any future logic)
    function operations() {
		
		 // Open modal on action button click
		$(".wallet-add").on("click", function () {
			
			const wallet_balance = $(this).data("val");
			const request_id = $(this).data("id"); 
			const driver_id = $(this).data("driver-id");
            const amount = $(this).data("amount");
			$("#wallet_balance").val(wallet_balance);
			$("#request_id").val(request_id);
			$("#driver_id").val(driver_id);
			$("#request_amount").val(amount);
			$("#documentModal").modal("show");
		});
		
		
		 // Submit form with confirmation
		$("#wallet_submit").on("click", function () {
			
			const amount = $("#request_amount").val();
            const driverid= $("#driver_id").val();
			const requestid= $("#request_id").val();
			const operation=$("#operation").val();
			const reason=$("#reason").val();
			const walletBalance = parseFloat($("#wallet_balance").val()); // Assuming wallet balance is available in a hidden input
            if (!amount || isNaN(amount) || amount <= 0) {
				toast("Amount is required and must be greater than zero.", "error");
				return;
			}

			if (!operation) {
				toast("Operation is required.", "error");
				return;
			}

			/*if (!reason) {
				toast("Reason is required.", "error");
				return;
			}*/
			
			if (!amount || amount <= 0) {
				toast("Amount should be greater than zero.", "error");
				return;
			}

			if (operation === "approved" && amount > walletBalance) {
				toast("Withdrawal amount cannot exceed the wallet balance.", "error");
				return;
			} 
			// Confirm before making AJAX call
			$.confirm({
				icon: "fa fa-warning",
				title: "Confirm!",
				content: "Are you sure you want to "+ operation +" this request?",
				theme: "modern",
				draggable: false,
				type: "red",
				typeAnimated: true,
				buttons: {
					confirm: function () {
						$.ajax({
							url: baseUrl + "/driver-earning/process/withdrawal-request",
							type: "POST",
							data: {
								driverid: driverid,
								requestid:requestid,
								amount: amount,
								walletBalance:walletBalance, 
								operation:operation,
								reason:reason
							},
							success: function (response) {
								if (response.success) {
									toast("Withdrawal request is "+operation, "success");
									$("#documentModal").modal("hide");

									setTimeout(function () {
										location.reload();
									}, 2000);
								} else {
									toast(response.error || "An error occurred while recording the Withdrawal request.", "error");
								}
							},
							error: function (xhr) {
								const errorMessage = xhr.responseJSON && xhr.responseJSON.message
									? xhr.responseJSON.message
									: "An error occurred while processing your request.";
								toast(errorMessage, "error");
							}
						});
					},
					cancel: function () {
						// Optional cancel action
					}
				}
			});
		});
	
		
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
                url: baseUrl + "/driver-earning/list",
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
