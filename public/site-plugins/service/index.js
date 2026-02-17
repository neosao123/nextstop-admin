$(document).ready(function () {
    getDataTable();
function getDataTable() {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dt-service")) {
        $("#dt-service").DataTable().clear().destroy();
    }
    var dataTable = $("#dt-service").DataTable({
        stateSave: false,
        lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: true,
        ajax: {
            url: baseUrl + "/service/list",
            type: "GET",
            data: {
            },
            complete: function (response) {
                //operations();
            },
        },
    });
}
});
