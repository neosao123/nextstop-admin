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
	
	$('#search_filter').on('click', function (e) {
        var driver_id = $("#driver_name").val();
		var customer_id = $("#customer_name").val();
		var vehicle_id = $("#vehicle").val();
		var coupon_id = $("#coupon_code").val();
        var goods_type_id = $("#goods_type").val();
        var unique_id = $("#unique_id").val();
		var from_date = $("#from_date").val();
		var to_date = $("#to_date").val();
        getDataTable(driver_id,customer_id,vehicle_id,coupon_id,goods_type_id,unique_id,from_date,to_date);
    });
	
	//clear button
    $("#reset_filter").click(function () {
        window.location.reload();
    });
	
	
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
        url: baseUrl + "/trips/exceldownload",
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
            link.download = 'Trips.csv';
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
        url: baseUrl + "/trips/pdfdownload",
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
            link.download = 'Trips.pdf';
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
            url: baseUrl + "/trips/fetch/customer",
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
            url: baseUrl + "/trips/fetch/driver",
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
            url: baseUrl + "/trips/fetch/vehicle",
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
            url: baseUrl + "/trips/fetch/coupon",
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
            url: baseUrl + "/trips/fetch/goods",
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
            url: baseUrl + "/trips/fetch/trip",
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
	
  
    getDataTable("", "");
    function operations() {
        //delete user
	
		$("a.btn-changestatus").on("click", function () {
			const id = $(this).data("id");

			$.confirm({
				icon: "fa fa-warning",
				title: "Confirm!",
				content: "Do you want to change status of trip?",
				theme: "modern",
				draggable: false,
				type: "red",
				typeAnimated: true,
				buttons: {
					confirm: function () {
						// Trigger the modal to open
						$('#documentModal').modal('show');

						// Reset the status and reason fields
						$('#status').val('');
						$('#reason').val('');
						$('#reason').parent().hide(); // Initially hide the reason field

						// Toggle reason field visibility based on status selection
						$('#status').on('change', function () {
							const selectedStatus = $(this).val();
							if (selectedStatus === 'cancelled') {
								$('#reason').parent().hide(); // Hide reason field for "Cancelled"
								$('#reason').val(''); // Clear the reason value
							} else if (selectedStatus === 'completed') {
								$('#reason').parent().show(); // Show reason field for "Completed"
							}
						});

						// Handle the Submit button click inside the modal
						$('#documentModal .btn-primary').off('click').on('click', function () {
							const status = $('#status').val();
							const reason = $('#reason').val();

							// Ensure status is selected
							if (!status) {
								toast("Please select a status.", "error");
								return;
							}

							// Ensure reason is provided if status is "Completed"
							if (status === 'completed' && !reason) {
								toast("Please provide a reason for completion.", "error");
								return;
							}

							// Proceed with the AJAX call
							$.ajax({
								url: baseUrl + "/trips/change-status/" + id,
								type: "get",
								data: {
									'_token': csrfToken,
									'status': status,
									'reason': reason,
								},
								success: function (response) {
									if (response.success) {
										toast("Trip status changed successfully.", "success");
										getDataTable();
										$('#documentModal').modal('hide');
									} else {
										toast(response.error || "An error occurred while changing the status.", "error");
									}
								},
								error: function (xhr) {
									const errorMessage = xhr.responseJSON && xhr.responseJSON.message
										? xhr.responseJSON.message
										: "An error occurred while processing your request.";
									toast(errorMessage, "error");
								}
							});
						});
					},
					cancel: function () {
						// Action to perform when cancel is clicked, if needed
					}
				}
			});
		});
		
		
		$("a.btn-refund-amount").on("click", function () {
			const id = $(this).data("id");

			$.confirm({
				icon: "fa fa-warning",
				title: "Confirm!",
				content: "Do you want to refund the amount for this trip?",
				theme: "modern",
				draggable: false,
				type: "red",
				typeAnimated: true,
				buttons: {
					confirm: function () {
						$.ajax({
							url: baseUrl + "/trips/refund/" + id,
							type: "GET",
							success: function (response) {
								if (response.success) {
									toast("Refund trip amount successfully.", "success");
									getDataTable();
								} else {
									toast(response.error || "An error occurred while refund trip amount .", "error");
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
						
					}
				}
			});
       });
	   
    }
  //trip list
   function getDataTable( driver_id, customer_id, vehicle_id, coupon_id, goods_type_id, unique_id, from_date, to_date) {
    $.fn.DataTable.ext.errMode = "none";

    if ($.fn.DataTable.isDataTable("#dt-trip")) {
        $("#dt-trip").DataTable().clear().destroy();
    }

    var dataTable = $("#dt-trip").DataTable({
        stateSave: true,
        lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: true,
        ajax: {
            url: baseUrl + "/trips/list",
            type: "GET",
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
            complete: function (response) {
                operations(); // Call the operations function after data is loaded
            },
        },
    });
}

});
