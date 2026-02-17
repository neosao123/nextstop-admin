$(function () {

    //on change of document verification status

    $("#document_verification_status").on("change", function () {
        const id = $("#porter_id").val();
        var status = $(this).val();

        $.confirm({
            icon: "fa fa-warning",
            content: "Do you want to change verification status?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: baseUrl + "/driver-document-details/change-status/" + id,
                        type: "GET",
                        data: {
                            '_token': csrfToken, // Include CSRF token
                            'status': status
                        },
                        success: function (response) {
                            if (response.success) {
                                console.log('data');
                                toast("Driver Document Status Changed Successfully.", "success");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while Driver document.", "error");
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

    // For JPEG, PNG, JPG
    $('.view-document-btn').on('click', function () {
        var documentUrl = $(this).data('url');
        $('#documentImage').attr('src', documentUrl);
        $('#documentModal').modal('show');
    });
    $('#documentModal').on('hidden.bs.modal', function () {
        $('#documentImage').attr('src', '');
    });


    // For PDF, DOCX
    $('.view-document-pdf-btn').on('click', function () {
        var documentUrl = $(this).data('url');
        var extension = documentUrl.split('.').pop().toLowerCase(); // Get the file extension

        if (extension === 'pdf') {
            // If it's a PDF, set the iframe src directly
            $('#documentIframe').attr('src', documentUrl);
        } else if (extension === 'docx') {
            // If it's a DOCX, use Google Docs viewer
            $('#documentIframe').attr('src', 'https://docs.google.com/gview?url=' + documentUrl + '&embedded=true');
        } else {
            // Handle unsupported file types (optional)
            alert('Unsupported file type!');
            return;
        }

        // var documentUrl = $(this).data('url');
        // $('#documentIframe').attr('src', documentUrl);
        $('#documentModalPDF').modal('show');
    });
    $('#documentModal').on('hidden.bs.modal', function () {
        $('#documentIframe').attr('src', '');
    });


});
