$(function () {

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    }); 

    getDataTable();
    function getDataTable() {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dt-enquiry")) {
            $("#dt-enquiry").DataTable().clear().destroy();
        }
        var dataTable = $("#dt-enquiry").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/enquiry/list",
                type: "GET",
                data: {
                 
                },
                complete: function (response) {
                    
                },
            },
        });
    }
});
