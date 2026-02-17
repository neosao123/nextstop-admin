const baseUrl = document.getElementsByTagName("meta").baseurl.content;
var chart1 = null;
var chart2 = null;
function getChartData() {
    $.ajax({
        url: baseUrl + "/dashboard/driver/piechart",
        method: "GET",
        dataType: 'JSON',
        success: function (response) {
            drawChart_1(response['data']['label'], response['data']['data'], response['data']['color']);
        }
    });
}

function updateChart(selected) {
    const frequency = selected.value;

    $.ajax({
        url: baseUrl + "/dashboard/totaltrips",
        method: "GET",
        data: { frequency: frequency },
        dataType: 'JSON',
        success: function(response) {
            drawChart_2(response['data']['xValues'], response['data']['yValues']);
        }
    });
}

function drawChart_2(x_Axis_Data, y_Axis_Data) {
    const ctx = document.getElementById("chart_2");
    if (chart2) chart2.destroy();
    chart2 = new Chart(ctx, {
        type: "bar",
        data: {
            labels: x_Axis_Data,
            datasets: [
                {
                    label: 'Trips',
                    data: y_Axis_Data,
                    backgroundColor: "#404E67",
                }
            ]
        },
        options: {
            responsive: true,
            legend: {
                display: false
            },
            scales: {
                xAxes: [{
                    barThickness: 45
                }]
            }
        }
    });
}

// Initial load with monthly data
updateChart(document.querySelector('input[value="monthly"]'));

function drawChart_1(label, data, color) {
    var ctx = document.getElementById("chart_1");
    if (chart1) chart1.destroy();
    if (label !== null || data !== undefined) {
        chart1 = new Chart(ctx, {
            type: "pie",
            data: {
                labels: label,
                datasets: [{
                    data: data,
                    backgroundColor: color,
                }]
            },
            options: {
                responsive: true,

                legend: {
                    display: false
                },
                title: {
                    display: false,
                }
            }
        });
    }
}

function getDriver() {
    $.fn.DataTable.ext.errMode = "none";

    if ($.fn.DataTable.isDataTable("#dt-verified-driver")) {
        $("#dt-verified-driver").DataTable().clear().destroy();
    }

    var dataTable = $("#dt-verified-driver").DataTable({
        stateSave: false,
        lengthChange: false, // This hides the "Show entries" dropdown
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: false,
		info: false,

        ajax: {
            url: baseUrl + "/dashboard/driver/list",
            type: "GET",
            data: {},
            complete: function (response) {

            },
        },
    });
}

function getCancelTrips() {
    $.fn.DataTable.ext.errMode = "none";

    if ($.fn.DataTable.isDataTable("#dt-cancel-trips")) {
        $("#dt-cancel-trips").DataTable().clear().destroy();
    }

    var dataTable = $("#dt-cancel-trips").DataTable({
        stateSave: false,
        lengthChange: false, // This hides the "Show entries" dropdown
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: false,
		info: false,

        ajax: {
            url: baseUrl + "/dashboard/canceltrips/list",
            type: "GET",
            data: {},
            complete: function (response) {

            },
        },
    });
}

$(document).ready(function () {
    getChartData();
    getDriver();
	getCancelTrips();
});