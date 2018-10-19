$(document).ready(function () {
    $('.input').each(function () {
        if (!$(this).hasClass("invalid")) {
            if ($(this).val() != "") {
                $(this).addClass("valid");
            }
        }
    });
    
    if ($("#hasCatering").is(":checked")) {
        $(".verpflegung").toggle();
    }

    M.updateTextFields();
    $("#hasCatering").change(function () {
        $(".verpflegung").toggle();
    });
});

function enterUsername() {
    var firstname = $("#firstname").val();
    var lastname = $("#lastname").val();

    var username = (firstname + "." + lastname).toLowerCase();

    $("#username").val(username);
    M.updateTextFields();
}