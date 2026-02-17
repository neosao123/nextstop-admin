const baseUrl = document.getElementsByTagName("meta").baseurl.content;
const btnSubmit = $("#btn-submit");

$(function () {
    // On Submit
    btnSubmit.on("click", function () {
        var formData = new FormData($("form#form-role")[0]);
        var formUrl = baseUrl + "/configuration/role/store";
        if ($("input#id").val() !== undefined && $("input#id").val() !== "") {
            formUrl = baseUrl + "/configuration/role/update";
        }
        $.ajax({
            type: "POST",
            url: formUrl,
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                btnSubmit.prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $(".invalid-feedback").remove();
                    $("form#form-role").addClass("was-invalid");
                    $.each(response.errors, function (i, v) {
                        let spnerr = '<div class="invalid-feedback">' + v[0] + "</div>";
                        $("[name='" + i + "']").after(spnerr);
                    });
                    return false;
                } else {
                    if (response.status === 200) {
                        toast(response.msg, "success");
                        $("#id").val("");
                        $("#form-title").text("New Role");
                        $("#btn-submit").text("Submit");
                        resetForm();
                        getDataTable();
                    } else {
                        toast("Something went wrong.", "error");
                        return false;
                    }
                }
            },
            error: function (error) {
                btnSubmit.removeAttr("disabled");
                toast("Something went wrong.", "error");
                return false;
            },
            complete: function () {
                btnSubmit.removeAttr("disabled");
            },
        });
    });

    // Datatable
    getDataTable();
});

function resetForm() {
    $("form#form-role").removeClass("was-invalid");
    $(".invalid-feedback").remove();
    $("form#form-role")[0].reset();
}

function operations() {
    // Delete Role
    $("a.btn-delete-role").on("click", function () {
     
        const role_id = $(this).data("role_id");
        $.confirm({
            icon: "fa fa-warning",
            title: "Confirm Delete!",
            content: "Do you want to delete!",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: baseUrl + "/configuration/role/delete/" + role_id,
                        type: "GET",
                        data: {},
                        dataType: "JSON",
                        complete: function (response) {
                            console.log(response);
                            if (response.responseJSON.status === 200) {
                                toast(response.responseJSON.message, "success");
                            }
                            if (response.responseJSON.status === 400) {
                                toast(response.responseJSON.message, "error");
                            }
                            $("#id").val("");
                            $("#form-title").text("New Role");
                            $("#btn-submit").text("Submit");
                            resetForm();
                            getDataTable();
                        },
                    });
                },
                cancel: function () { },
            },
        });
    });

    // Edit role
    $("a.btn-edit-role").on("click", function () {
        const role_id = $(this).data("role_id");
        $.ajax({
            type: "GET",
            url: baseUrl + "/configuration/role/edit",
            data: {
                id: role_id,
            },
            dataType: "json",
            success: function (response) {
                if (response.status === 200) {
                    $("#form-title").text("Update Role");
                    $("#btn-submit").text("Update");
                    $("#name").val(response.data.name);
                    $("#id").val(response.data.id);
                }
            },
        });
    });
}

function getDataTable() {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dt-role")) {
        $("#dt-role").DataTable().clear().destroy();
    }
    var dataTable = $("#dt-role").DataTable({
        stateSave: false,
        lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
        processing: true,
        serverSide: true,
        ordering: false,
        searching: true,
        paging: true,
        ajax: {
            url: baseUrl + "/configuration/role/list",
            type: "GET",
            data: {},
            complete: function (response) {
                operations();
            },
        },
    });
}
