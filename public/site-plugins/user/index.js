$(function () {

    //searching user
	
	$('#search_filter').on('click', function (e) {
        var user_name = $("#user_name").val();
        var role_id = $("#role_id").val();

        getDataTable(user_name, role_id);
    });
	
	//clear button
    $("#reset_filter").click(function () {
        window.location.reload();
    });
	
	
	$("#btnExcelDownload").on("click", function (e) {
		var user_name = $("#user_name").val();
		var role_id = $("#role_id").val();

		$.ajax({
			type: "get",
			url: baseUrl + "/users/exceldownload",
			data: {
				user: user_name,
				role: role_id
			},
			xhrFields: {
				responseType: 'blob'
			},
			success: function (response) {
				var blob = new Blob([response], { type: 'text/csv' });
				var link = document.createElement('a');
				link.href = window.URL.createObjectURL(blob);
				link.download = 'Users.csv';
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
        var user_name = $("#user_name").val();
        var role_id = $("#role_id").val();

        $.ajax({
            type: "get",
            url: baseUrl + "/users/pdfdownload",
            data: {
                export: 1,
                user: user_name,
                role: role_id
            },
            xhrFields: {
                responseType: 'blob' // This is important to handle the response as a blob
            },
            success: function (response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Users.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
        });
    });


   
	// Select2 - Role 
    $('#role_id').select2({
        placeholder: 'Select Role',
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
            url: baseUrl + "/users/fetch/role",
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
	
	
	// Select2 - Users 
    $('#user_name').select2({
        placeholder: 'Select User',
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
            url: baseUrl + "/users/fetch/users",
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
	
		$("a.btn-delete").on("click", function () {
			const id = $(this).data("id");

			$.confirm({
				icon: "fa fa-warning",
				title: "Confirm Delete!",
				content: "Do you want to delete this user?",
				theme: "modern",
				draggable: false,
				type: "red",
				typeAnimated: true,
				buttons: {
					confirm: function () {
						$.ajax({
							url: baseUrl + "/users/" + id,
							type: "DELETE",
							data: {
								'_token': csrfToken,
							},
							success: function (response) {
								if (response.success) {
									toast("User deleted successfully.", "success");
									getDataTable();
								} else {
									toast(response.error || "An error occurred while deleting the user.", "error");
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
					content: "Do you want to unblock this user?",
					theme: "modern",
					draggable: false,
					type: "red",
					typeAnimated: true,
					buttons: {
						confirm: function () {
							$.ajax({
								url: baseUrl + "/users/block/" + id, 
								type: "GET",
								
								success: function (response) {
									if (response.success) {
										toast("User unblock successfully.", "success");
										getDataTable();
									} else {
										toast(response.error || "An error occurred while unblock user.", "error");
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
					content: "Do you want to block this user?",
					theme: "modern",
					draggable: false,
					type: "red",
					typeAnimated: true,
					buttons: {
						confirm: function () {
							$.ajax({
								url: baseUrl + "/users/block/" + id, 
								type: "GET",
								
								success: function (response) {
									if (response.success) {
										toast("User block successfully.", "success");
										getDataTable();
									} else {
										toast(response.error || "An error occurred while user block.", "error");
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
		
    }
  //user list
    function getDataTable(user_name, role_id) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dt-users")) {
            $("#dt-users").DataTable().clear().destroy();
        }
        var dataTable = $("#dt-users").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/users/list",
                type: "GET",
                data: {
                    user: user_name,
                    role: role_id
                },
                complete: function (response) {
                    operations();
                },
            },
        });
    }
});
