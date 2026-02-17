
// Toast Message
function toast(msg, status, duration = '', position = '', gravity = '') {
    let siteBaseUrl = document.getElementsByTagName("meta").baseurl.content;
    let linearGradient, avatar;

    switch (status) {
        case "success":
            linearGradient = "linear-gradient(120deg, #b1ea4d 0%, #459522 100%)";
            avatar = siteBaseUrl + '/assets/img/icons/toast-icons/success.png';
            break;
        case "error":
            linearGradient = "linear-gradient(120deg, #f77062 0%, #fe5196 100%)";
            avatar = siteBaseUrl + '/assets/img/icons/toast-icons/error.png';
            break;
        case "warning":
            linearGradient = "linear-gradient(120deg, #fc6076 0%, #ff9a44 100%)";
            avatar = siteBaseUrl + '/assets/img/icons/toast-icons/warning.png';
            break;
        case "info":
            linearGradient = "linear-gradient(120deg, #00c6fb 0%, #005bea 100%)";
            avatar = siteBaseUrl + '/assets/img/icons/toast-icons/info.png';
            break;
        default:
            linearGradient = "linear-gradient(120deg, #e4efe9 0%, #93a5cf 100%)";
            avatar = siteBaseUrl + '/assets/img/icons/toast-icons/default.png';
            break;
    }

    Toastify({
        text: msg,
        duration: (Number(duration) * 1000) || 5000,
        newWindow: true,
        close: true,
        gravity: gravity || "top",
        position: position || "right",
        stopOnFocus: true,
        close: false,
        avatar: avatar,
        style: {
            background: linearGradient,
        },
        onClick: function () { },
    }).showToast();
}


$(function () {
    'use-strict';
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    $(".navbar-vertical-toggle").on("click", function () {
        $("a.app-name").toggle("show");
    });
});

