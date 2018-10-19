$(window).on('load', function () {

    $('select').formSelect();
    var employees = $(".employee");
    if (employees.length == 0) {
        // $("#add_employee").toggleClass("fadeIn animated show");
        openAddEmployee()
    }

    $("#popup_background").click(function (e) {
        if ($(e.target).is('#popup_background')) {
            closeAddEmployee();
        }
    });
});


var url = window.location.href.split('/');
var rapportid = url[url.length - 1];

function openAddEmployee() {
    $("#popup_background").attr("class", "fadeIn animated show");
    $("#add_employee").attr("class", "zoomIn animated show form");

    var windowHeight = $(window).height();
    var windowWidth = $(window).width();
    var popupWidth = $("#add_employee").width();
    var popupHeight = $("#add_employee").height();
    // var popupHeight = document.getElementById("add_employee").offsetHeight;

    $("#add_employee").css({
        'left': (windowWidth - popupWidth) / 2 + "px",
        'top': (windowHeight - popupHeight) / 2 + "px"
    });

    $(".employee-input").each(function (thisElement) {
        // console.log($(this).val())
        if ($("#" + $(this).val()).length != 0) {
            $(this).attr('disabled', 'disabled');
        }
    });

}

function closeAddEmployee() {
    $("#popup_background").attr("class", "fadeOut animated");
    $("#add_employee").attr("class", "zoomOut animated form");

    function hideElements() {
        $("#popup_background").addClass("hide");
        $("#add_employee").addClass("hide");
    }

    setTimeout(hideElements, 400);
}

function updateEmployees() {
    var checkedEmployees = Array();
    $('.employees-checkbox :checked').each(function () {
        employeeId = $(this).val();
        checkedEmployees.push(employeeId);

        var employeeName = $(this).parent().find("span").text();

        $("<tr>").attr('id', employeeId).addClass('employee').appendTo('.employees');
        var HtmlEmployeeHeader = [
            '<th>',
            '<p>' + employeeName + '</p>',
            '<a onclick="deleteEmployee(' + employeeId + ')" class="waves-effect waves-light btn-small red">Entfernen</a>',
            '</th>',
        ].join('\n');
        $("#" + employeeId).append(HtmlEmployeeHeader);
        for (var i = 0; i < 6; i++) {
            var HtmlElement = [
                '<td class="employee-day">',
                '<div class="row small-row">',
                '<div class="input-field col s12 small-input">',
                '<input id="hours_' + i + '_' + employeeId + '" type="number" class="validate hours_' + i + '" onblur="updateHours(' + i + ',' + employeeId + ', this)">',
                '<label for="hours_' + i + '_' + employeeId + '">Stunden</label>',
                '</div>',
                '</div> ',
                '<div class="row small-row">',
                '<div class="input-field col s12">',
                '<select onchange="updateFood(' + i + ',' + employeeId + ', this)">',
                '<option value="" disabled selected>Verpflegung wählen</option>',
                '<option value="1">Eichhof</option>',
                '<option value="2">Kunde</option>',
                '<option value="3">Keine Angabe</option>',
                '</select>',
                '<label>Verpflegung</label>',
                '</div>',
                '</div>',
                '<div class="row small-row">',
                '<div class="input-field col s12">',
                '<select onchange="updateProject({{$i}}, {{$employeeCollection[0]->employee->id}}, this)" class="project_{{$i}}">',
                '<option value="" disabled selected>Projekt wählen</option>',
                '</select>',
                '<label>Projekt</label>',
                '</div>',
                '</div>',
                '</td>',
            ].join('\n');
            $("#" + employeeId).append(HtmlElement);
        }
    });
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/rapport/" + rapportid,
        data: {
            type: 'addEmployee',
            employees: checkedEmployees
        },
        type: 'patch',
        success: function (data) {
            window.location.reload();
        },
        error: function (data) {}
    });

    $('select').formSelect();
    closeAddEmployee();
    updateTotalHours();
}

function updateHours(day, employeeId, thisElement) {

    var value = thisElement.value;
    var data = {
        type: 'hours',
        day: day,
        employeeId: employeeId,
        hours: thisElement.value
    }

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/rapport/" + rapportid,
        data: data,
        type: 'patch',
        success: function (data) {

        },
        error: function (data) {}
    });
    var hourElements = $(".hours_" + day);
    var totalHours = 0;
    hourElements.each(function () {
        var value = parseInt($(this).val());
        totalHours += isNaN(value) ? 0 : value;
        $("#totalhours_" + day).text(totalHours);
    });
}

function updateTotalHours() {
    for (var i = 0; i < 6; i++) {
        var hourElements = $(".hours_" + i);
        var totalHours = 0;
        hourElements.each(function () {
            var value = parseInt($(this).val());
            totalHours += isNaN(value) ? 0 : value;
            $("#totalhours_" + i).text(totalHours);

        });
    }

}

function updateFood(day, employeeId, thisElement) {
    var foodType = thisElement.value;
    console.log(foodType);
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/rapport/" + rapportid,
        data: {
            type: 'food',
            day: day,
            employeeId: employeeId,
            foodType: foodType
        },
        type: 'patch',
        success: function (data) {

        },
        error: function (data) {}
    });
}

function updateAllProjects(day, thisElement) {
    var projectId = thisElement.value;
    if (projectId == "0") {
        addProject();
    } else {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/rapport/" + rapportid,
            data: {
                type: 'projectAll',
                day: day,
                projectId: projectId
            },
            type: 'patch',
            success: function (data) {

            },
            error: function (data) {}
        });
        console.log(day);
        $(".project_" + day).val(projectId);
        $('select').formSelect();
    }
}

function updateProject(day, employeeId, thisElement) {
    var projectId = thisElement.value;
    if (projectId == "0") {
        addProject();
    } else {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/rapport/" + rapportid,
            data: {
                type: 'project',
                day: day,
                projectId: projectId,
                employeeId: employeeId
            },
            type: 'patch',
            success: function (data) {

            },
            error: function (data) {}
        });
    }
}

function updateComment(commentDay, thisElement) {
    var comment = thisElement.value;
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/rapport/" + rapportid,
        data: {
            type: 'comment',
            commentDay: commentDay,
            comment: comment
        },
        type: 'patch',
        success: function (data) {

        },
        error: function (data) {}
    });

}

function deleteEmployee(employeeId) {
    swal({
        title: 'Wollen sie diesen Mitarbeiter wirklich entfernen?',
        buttons: {
            cancel: "Nein",
            confirm: "Ja, löschen"
        }
    }).then((result) => {
        if (result) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "/rapport/" + rapportid,
                data: {
                    type: 'deleteEmployee',
                    employeeId: employeeId
                },
                type: 'patch',
                success: function (data) {
                    $("#" + employeeId).remove();
                    updateTotalHours();
                },
                error: function (data) {}
            });
        }

    });
}

function updateCheckbox(thisElement) {
    var inhalt;
    if ($(thisElement).is(":checked")) {
        inhalt = 1;
    } else {
        inhalt = 0;
    }
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/rapport/" + rapportid,
        type: 'patch',
        data: {
            type: $(thisElement).attr("name"),
            data: inhalt
        },
        success: function (data) {
            console.log(data);
        },
        error: function (data) {

        }
    });
}

function deleteRapport() {
    swal({
        title: 'Wollen sie den Rapport wirklich löschen?',
        buttons: {
            cancel: "Nein",
            confirm: "Ja, Löschen"
        }
    }).then((result) => {
        console.log(result);
        if (result) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "/rapport/" + rapportid,
                type: 'delete',
                success: function (data) {
                    window.location = "/rapport/show";
                },
                error: function (data) {

                }
            });
        }
    });
}

function addProject() {
    $("#project_popup_background").attr("class", "fadeIn animated show");
    $("#add_project").attr("class", "zoomIn animated show form");

    var windowHeight = $(window).height();
    var windowWidth = $(window).width();
    var popupWidth = $("#add_project").width();
    var popupHeight = $("#add_project").height();
    // var popupHeight = document.getElementById("add_employee").offsetHeight;

    $("#add_project").css({
        'left': (windowWidth - popupWidth) / 2 + "px",
        'top': (windowHeight - popupHeight) / 2 + "px"
    });
    createProjectInput();
}