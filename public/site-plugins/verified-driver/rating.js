$(function () {
    // Initial driver value
    var driver = $("#driver").val();

    // Search button functionality
    $('#search_filter').on('click', function () {
        var customer = $("#customer").val();
        var driver = $("#driver").val();
        var trip = $("#trip").val();
        getDataTable(customer, trip, driver);
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
            url: baseUrl + "/driver/verified/fetch/trip",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: params => ({ search: params.term }),
            processResults: response => ({ results: response }),
            cache: true
        }
    });

    // Initialize Select2 for Customer
    $('#customer').select2({
        placeholder: 'Select Customer',
        allowClear: true,
        minimumInputLength: 1,
        language: {
            inputTooShort: () => 'Please enter 1 or more characters',
            searching: () => 'Searching...',
            noResults: () => 'No data found'
        },
        ajax: {
            url: baseUrl + "/driver/verified/fetch/customer",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: params => ({ search: params.term }),
            processResults: response => ({ results: response }),
            cache: true
        }
    });

    // Initialize data table
    getDataTable("", "", driver);

    // Operations (placeholder for any future logic)
    function operations() {}

    // Function to load customer rating list into DataTable
    function getDataTable(customer, trip, driver) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dt-rating")) {
            $("#dt-rating").DataTable().clear().destroy();
        }

        $("#dt-rating").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/driver/verified/rating/list",
                type: "GET",
                data: { customer, driver, trip },
                complete: () => operations()
            }
        });
    }
});
