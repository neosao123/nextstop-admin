$(function () {

    //searching user
	
	$('#search_filter').on('click', function (e) {
        var driver = $("#driver").val();      
        getDataTable(driver);
    });
	
	//clear button
    $("#reset_filter").click(function () {
        window.location.reload();
    });
	
	
	$("#btnExcelDownload").on("click", function (e) {
		var driver = $("#driver").val();    
		$.ajax({
			type: "get",
			url: baseUrl + "/incentives/exceldownload",
			data: {
				driver:driver
			},
			xhrFields: {
				responseType: 'blob'
			},
			success: function (response) {
				var blob = new Blob([response], { type: 'text/csv' });
				var link = document.createElement('a');
				link.href = window.URL.createObjectURL(blob);
				link.download = 'Incentives.csv';
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
        $.ajax({
            type: "get",
            url: baseUrl + "/incentives/pdfdownload",
            data: {
                export: 1,
                driver:driver
            },
            xhrFields: {
                responseType: 'blob' // This is important to handle the response as a blob
            },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Incentives.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
        });
    });


   
	// Select2 - Driver 
    $('#driver').select2({
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
            url: baseUrl + "/incentives/fetch/driver",
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
        //delete incentives
	
		$("a.btn-delete").on("click", function () {
			const id = $(this).data("id");

			$.confirm({
				icon: "fa fa-warning",
				title: "Confirm Delete!",
				content: "Do you want to delete this incentives?",
				theme: "modern",
				draggable: false,
				type: "red",
				typeAnimated: true,
				buttons: {
					confirm: function () {
						$.ajax({
							url: baseUrl + "/incentives/" + id,
							type: "DELETE",
							data: {
								'_token': csrfToken,
							},
							success: function (response) {
								if (response.success) {
									toast("Incentives deleted successfully.", "success");
									getDataTable();
								} else {
									toast(response.error || "An error occurred while deleting the incentives.", "error");
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
  //incentives list
    function getDataTable(driver) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dt-incentives")) {
            $("#dt-incentives").DataTable().clear().destroy();
        }
        var dataTable = $("#dt-incentives").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/incentives/list",
                type: "GET",
                data: {
                    driver: driver
                },
                complete: function (response) {
                    operations();
                },
            },
        });
    }
});
