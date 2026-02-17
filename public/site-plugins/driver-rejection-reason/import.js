function Upload() {
    var fileUpload = $("#uploadFile")[0]; // Access the DOM element directly

    if (fileUpload.value !== '') {
        var file = fileUpload.files[0];
        var allowedExtensions = /(\.xls|\.xlsx)$/i; // Accept Excel file extensions
        var mimeTypes = ["application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"];

        // Validate file type and extension
        if (!allowedExtensions.test(file.name) || !mimeTypes.includes(file.type)) {
            toast('Please upload a valid Excel file.', 'error')
            fileUpload.value = '';
            $('#driverRejectionReasonList').addClass('d-none').hide();
            return;
        }

        if (typeof FileReader !== "undefined") {
            // Reset the table and progress bar before loading new file
            $('#driverRejectionReasonList').removeClass('d-none').show();
            $("#file-progress-bar").width('0%').html('0%');
            $('#successMsg').html(''); // Reset success message
            $('#tableID').remove(); // Remove previous table
            $('#dataSubmit').attr('disabled', false);

            var reader = new FileReader();
            if (reader.readAsBinaryString) {
                reader.onload = function (e) {
                    ProcessExcel(e.target.result);
                };
                reader.readAsBinaryString(file);
            } else {
                reader.onload = function (e) {
                    var data = "";
                    var bytes = new Uint8Array(e.target.result);
                    for (var i = 0; i < bytes.byteLength; i++) {
                        data += String.fromCharCode(bytes[i]);
                    }
                    ProcessExcel(data);
                };
                reader.readAsArrayBuffer(file);
            }
        } else {
            toast('This browser does not support HTML5.', 'error');
            $('#driverRejectionReasonList').addClass('d-none').hide();
        }
    } else {
        toast('The excel file is required.', 'error')
        $('#driverRejectionReasonList').addClass('d-none').hide();
    }
}

function ProcessExcel(data) {
    var workbook = XLSX.read(data, {
        type: 'binary'
    });
    var firstSheet = workbook.SheetNames[0];
    var excelRows = XLSX.utils.sheet_to_json(workbook.Sheets[firstSheet]);

    var table = document.createElement("table");
    table.border = "1";
    table.id = "tableID";
    table.setAttribute('class', "table table-responsive table-sm table-striped");

    // Insert header row
    var headers = [
        "Sr No", "Reason", "Status"
    ];

    var headerRow = table.insertRow(-1);
    headers.forEach(function (header) {
        var headerCell = document.createElement("TH");
        headerCell.innerHTML = header;
        headerRow.appendChild(headerCell);
    });

    // Insert data rows
    for (var i = 0; i < excelRows.length; i++) {
        var row = table.insertRow(-1);

        row.insertCell(-1).innerHTML = (i + 1); // Sr No
        row.insertCell(-1).innerHTML = excelRows[i]["Reason"] || "";
        row.insertCell(-1).innerHTML = excelRows[i]["Status"] || "";
    }

    var dvExcel = document.getElementById("dvExcel");
    dvExcel.innerHTML = "";
    dvExcel.appendChild(table);
    validateExcelRows();

    var trowCount = document.getElementById('tableID').rows.length;
    var Excelcount = excelRows.length;
}


function validateExcelRows() {
    var convertedIntoArray = [];

    $("table#tableID tr").each(function (index) {
        if (index > 0) {
            var rowDataArray = [];
            var actualData = $(this).find('td');

            if (actualData.length > 0) {
                actualData.each(function () {
                    rowDataArray.push($(this).text().trim());
                });
                convertedIntoArray.push(rowDataArray);
            }
        }
    });

    $.ajax({
        url: baseUrl + "/driver-rejection-reason/validate/excel",
        async: false,
        dataType: 'JSON',
        type: 'POST',
        data: {
            'convertedIntoArray': convertedIntoArray
        },
        success: function (response) {
            $("table#tableID tr").css('background-color', '');

            if (response.msg === '') {
                $('#dataSubmit').css('display', 'inline');
                $('#message1').css('display', 'none');
                document.getElementById('validMsgs').innerHTML = '';
                $('#rowExcepts').val('');
                $('#dataSubmit').text('Submit');
            } else {
                $('#message1').css('display', 'block');
                $('#rowExcepts').val(response.rowArr);
                document.getElementById('validMsgs').innerHTML = response.msg;
                $('#dataSubmit').css('display', 'none');

                var invalidRows = JSON.parse(response.rowArr);
                /*$.each(invalidRows, function(index, val) {
                    $("table#tableID tr").eq(val + 1).css('background-color', '#F68D0F');
                });*/
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            $('#message1').css('display', 'block').text("An error occurred while validating.");
            $('#dataSubmit').css('display', 'none');
        }
    });
}

$('#submitForm').on('submit', function (e) {
    e.preventDefault();
    $('#message1').css('display', 'none');
    var file_data = $('#uploadFile').prop('files')[0];
    var noOfRecords = $('#tableID tr').length;
    var rowExcepts = $('#rowExcepts').val()
    var formData = new FormData($("form#upload_form")[0]);
    formData.append('tableLength', noOfRecords);
    formData.append('uploadFile', file_data);
    formData.append('rowExcepts', rowExcepts);
    $.ajax({
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function (element) {
                if (element.lengthComputable) {
                    var percentComplete = Math.round((element.loaded / element.total) * 100);
                    $("#file-progress-bar").width(percentComplete + '%');
                    $("#file-progress-bar").html(percentComplete + '%');
                }
            }, false);
            return xhr;
        },
        type: 'POST',
        url: baseUrl + '/driver-rejection-reason/upload/excel',
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        dataType: 'json',
        beforeSend: function () {
            $('#dataSubmit').attr('disabled', 'disabled');
            $('#dataSubmit').text('Saving');
            $('#process').css('display', 'block');
            $("#file-progress-bar").width('0%');
        },
        success: function (response) {
            $('#process').css('display', 'none');
            if (response.status == true) {
                $('#successMsg').html('<div class="alert alert-success text-center">' + response.text + '</div>');
                $('#dataSubmit').css('display', 'none');
                $('#uploadFile').val('');

                // Redirect after 5 seconds (5000ms)
                setTimeout(function () {
                    window.location.href = baseUrl + '/driver-rejection-reason';
                }, 8000);

            } else {
                $('#dataSubmit').css('display', 'inlline');
                $('#successMsg').html('<div class="alert alert-success text-center">' + response.text + '</div>');
                $('#dataSubmit').attr('disabled', false);
                $('#dataSubmit').text('Save');
            }
        }
    })
});


$(document).ready(function () {
    var clear_timer;
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });

});