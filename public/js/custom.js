$(document).ready(() => {
    $('[data-toggle="tooltip"]').tooltip();

    $(".nav-sidebar .nav-link").each((index, item) => {
        var navLink = $(item);
        if (window.location.pathname.indexOf(navLink.attr("href")) > -1)
            navLink.addClass("active");
    });

    $("#password-eye").click(() => {
        $("#password-eye").toggleClass("fa-eye-slash");
        $("#password-input").attr("type", (_, attr) =>
            attr == "password" ? "text" : "password"
        );
    });
});
