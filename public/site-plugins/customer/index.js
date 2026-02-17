$(function () {

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    }); 

	
	//searching customer
	
	$('#search_filter').on('click', function (e) {
        var customer_name = $("#customer_name").val();
        var email = $("#email").val();
        var phone_number = $("#phone_number").val();
		var account_status = $("#account_status").val();
        getDataTable(customer_name, email,phone_number,account_status);
    });
	
	//clear button
    $("#reset_filter").click(function () {
        window.location.reload();
    });
	
	
	$("#btnExcelDownload").on("click", function (e) {
		var customer_name = $("#customer_name").val();
        var email = $("#email").val();
        var phone_number = $("#phone_number").val();
		var account_status = $("#account_status").val();
		$.ajax({
			type: "get",
			url: baseUrl + "/customers/exceldownload",
			data: {
				customer_name: customer_name,
				email: email,
				phone_number:phone_number,
				account_status:account_status
			},
			xhrFields: {
				responseType: 'blob'
			},
			success: function (response) {
				var blob = new Blob([response], { type: 'text/csv' });
				var link = document.createElement('a');
				link.href = window.URL.createObjectURL(blob);
				link.download = 'Customers.csv';
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
        var customer_name = $("#customer_name").val();
        var email = $("#email").val();
        var phone_number = $("#phone_number").val();
	    var account_status = $("#account_status").val();
        $.ajax({
            type: "get",
            url: baseUrl + "/customers/pdfdownload",
            data: {
                export: 1,
                customer_name: customer_name,
				email: email,
				phone_number:phone_number,
				account_status:account_status
            },
            xhrFields: {
                responseType: 'blob' // This is important to handle the response as a blob
            },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Customers.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
        });
    });
	
	// Select2 -Customer 
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
            url: baseUrl + "/customers/fetch/customer",
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
	
	
	// Select2 -Email 
    $('#email').select2({
        placeholder: 'Select Email',
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
            url: baseUrl + "/customers/fetch/email",
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
	
	
	// Select2 -phone number
    $('#phone_number').select2({
        placeholder: 'Select Phone Number',
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
            url: baseUrl + "/customers/fetch/mobile",
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
	

  
    getDataTable("", "","");
    function operations() {
        //delete customer
	
		$("a.btn-delete").on("click", function () {
			const id = $(this).data("id");

			$.confirm({
				icon: "fa fa-warning",
				title: "Confirm Delete!",
				content: "Do you want to delete this customer?",
				theme: "modern",
				draggable: false,
				type: "red",
				typeAnimated: true,
				buttons: {
					confirm: function () {
						$.ajax({
							url: baseUrl + "/customers/" + id,
							type: "DELETE",
							data: {
								'_token': csrfToken,
							},
							success: function (response) {
								if (response.success) {
									toast("Customer deleted successfully.", "success");
									getDataTable();
								} else {
									toast(response.error || "An error occurred while deleting the customer.", "error");
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
	   
	   
	   $("a.btn-block").on("click", function () {
		   
			const id = $(this).data("id");
            var isBlock=$(this).data("val");
			if(isBlock==1){
				$.confirm({
					icon: "fa fa-warning",
					title: "Confirm!",
					content: "Do you want to unblock this customer?",
					theme: "modern",
					draggable: false,
					type: "red",
					typeAnimated: true,
					buttons: {
						confirm: function () {
							$.ajax({
								url: baseUrl + "/customers/block/" + id, 
								type: "GET",
								
								success: function (response) {
									if (response.success) {
										toast("Customer unblock successfully.", "success");
										getDataTable();
									} else {
										toast(response.error || "An error occurred while unblock customer.", "error");
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
			}
			if(isBlock==0){
				$.confirm({
					icon: "fa fa-warning",
					title: "Confirm!",
					content: "Do you want to block this customer?",
					theme: "modern",
					draggable: false,
					type: "red",
					typeAnimated: true,
					buttons: {
						confirm: function () {
							$.ajax({
								url: baseUrl + "/customers/block/" + id, 
								type: "GET",
								
								success: function (response) {
									if (response.success) {
										toast("Customer block successfully.", "success");
										getDataTable();
									} else {
										toast(response.error || "An error occurred while customer block.", "error");
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
			}
			
       });
	   
	   
	   // Open modal on action button click
		$(".wallet-add").on("click", function () {
			const wallet_balance=$(this).data("val");
			const customerid = $(this).data("id");
            $("#wallet_balance").val(wallet_balance);
			$("#customer_id").val(customerid);
			$("#documentModal").modal("show");  
		});

	
	    // Submit form with confirmation
		$("#wallet_submit").on("click", function () {
			const amount = $("#amount").val();
            const customerid= $("#customer_id").val();
			const operation=$("#operation").val();
			const reason=$("#reason").val();
			const walletBalance = parseFloat($("#wallet_balance").val()); // Assuming wallet balance is available in a hidden input
            if (!amount || isNaN(amount) || amount <= 0) {
				toast("Amount is required and must be greater than zero.", "error");
				return;
			}

			if (!operation) {
				toast("Operation is required. Please select 'Add' or 'Deduction'.", "error");
				return;
			}

			if (!reason) {
				toast("Reason is required.", "error");
				return;
			}
			
			if (!amount || amount <= 0) {
				toast("Amount should be greater than zero.", "error");
				return;
			}

			if (operation === "deduction" && amount > walletBalance) {
				toast("Deduction amount cannot exceed the wallet balance.", "error");
				return;
			} 
			// Confirm before making AJAX call
			$.confirm({
				icon: "fa fa-warning",
				title: "Confirm!",
				content: "Are you sure you want to add wallet amount?",
				theme: "modern",
				draggable: false,
				type: "red",
				typeAnimated: true,
				buttons: {
					confirm: function () {
						$.ajax({
							url: baseUrl + "/customers/wallet",
							type: "POST",
							data: {
								customerid: customerid,
								amount: amount,
								operation:operation,
								reason:reason
							},
							success: function (response) {
								if (response.success) {
									toast("Customer wallet recorded successfully", "success");
									$("#documentModal").modal("hide");

									setTimeout(function () {
										location.reload();
									}, 2000);
								} else {
									toast(response.error || "An error occurred while recording the customer wallet.", "error");
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
  //customer list
    function getDataTable(customer_name, email,phone_number,account_status) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dt-customer")) {
            $("#dt-customer").DataTable().clear().destroy();
        }
        var dataTable = $("#dt-customer").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/customers/list",
                type: "GET",
                data: {
                    customer: customer_name,
					phone_number:phone_number,
                    email:email,
					account_status:account_status,
                },
                complete: function (response) {
                    operations();
                },
            },
        });
    }
});
