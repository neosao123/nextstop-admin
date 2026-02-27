$(function () {
    var urlParams = new URLSearchParams(window.location.search);
    var filter = urlParams.get('filter');
    var isPaging = true;

    if (filter === 'today') {
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var yyyy = today.getFullYear();
        var todayStr = dd + '-' + mm + '-' + yyyy;
        
        $("#from_date").val(todayStr);
        $("#to_date").val(todayStr);
        isPaging = false;
    } else if (filter === 'month') {
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        
        var firstDay = '01-' + mm + '-' + yyyy;
        
        var lastDayObj = new Date(yyyy, today.getMonth() + 1, 0);
        var lastDd = String(lastDayObj.getDate()).padStart(2, '0');
        var lastDay = lastDd + '-' + mm + '-' + yyyy;

        $("#from_date").val(firstDay);
        $("#to_date").val(lastDay);
        isPaging = false;
    }

    $("#from_date").flatpickr({
        dateFormat: "d-m-Y",
        allowInput: true
    });
    $("#to_date").flatpickr({
        dateFormat: "d-m-Y",
        allowInput: true
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
            url: baseUrl + "/reports/commission/fetch/driver",
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

    getDataTable();

    $('#search_filter').on('click', function (e) {
        getDataTable();
    });

    $("#reset_filter").click(function () {
        $("#driver").val(null).trigger('change');
        $("#from_date").val("");
        $("#to_date").val("");

        if (filter === 'today' || filter === 'month') {
            window.history.pushState({}, document.title, window.location.pathname);
            filter = null;
            isPaging = true;
        }

        getDataTable();
    });

    $("#btnExcelDownload").on("click", function (e) {
        var driver = $("#driver").val();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();

        $.ajax({
            type: "get",
            url: baseUrl + "/reports/commission/excel-download",
            data: {
                driver: driver,
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
                link.download = 'Commission_Report.csv';
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
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();

        $.ajax({
            type: "get",
            url: baseUrl + "/reports/commission/pdf-download",
            data: {
                driver: driver,
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
                link.download = 'Commission_Report.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
        });
    });

    function getDataTable() {
        var driver = $("#driver").val();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();

        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dt-commission")) {
            $("#dt-commission").DataTable().clear().destroy();
        }
        var dataTable = $("#dt-commission").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: isPaging,
            columnDefs: [
                { className: "text-start", targets: [0, 1, 2] },
                { className: "text-end", targets: [3, 4, 5] },
                { className: "text-center", targets: [6] }
            ],
            ajax: {
                url: baseUrl + "/reports/commission/list",
                type: "GET",
                data: {
                    driver: driver,
                    from_date: from_date,
                    to_date: to_date
                }
            },
            drawCallback: function (settings) {
                var api = this.api();
                var count = api.page.info().recordsDisplay;
                if (count === 0) {
                    $('#btnExcelDownload').prop('disabled', true);
                    $('#btnPdfDownload').prop('disabled', true);
                } else {
                    $('#btnExcelDownload').prop('disabled', false);
                    $('#btnPdfDownload').prop('disabled', false);
                }

                // Show limit-wise total dynamically sent from backend inside table footer
                var response = settings.json;
                var total = (response && response.totalCommissionLimit) ? '₹ ' + response.totalCommissionLimit : '₹ 0.00';
                $(api.column(4).footer()).html('<b>' + total + '</b>');
            }
        });
    }
});
