$(document).ready(function () {
    getDataTable();
})
function getDataTable() {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dt-setting")) {
        $("#dt-setting").DataTable().clear().destroy();
    }
    var dataTable = $("#dt-setting").DataTable({
        stateSave: false,
        lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: true,
        ajax: {
            url: baseUrl + "/setting/list",
            type: "GET",
            data: {},
            complete: function (response) {
                //operations();
            },
        },
    });
}
