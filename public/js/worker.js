let scrollbar = document.getElementsByClassName("calendar")[0];

let scrollbarHeight = $(".calendar").height();
let calendarHeight = $(".calendar-container").height();
let scrollHeight = (calendarHeight - scrollbarHeight) / 2;

scrollbar.scrollTo(0, scrollHeight);
$('select').formSelect();

var options = {
    twelveHour: false
};
var elems = document.querySelectorAll('.timepicker');
var timepickers = M.Timepicker.init(elems, options);

for(let timepicker of timepickers){
    timepicker.el.onfocus = () => {
        timepicker.el.blur();
    }
}

function selectDate(){
    window.location = "/time?date=" + datePicker.date.getDate() +  "." 
    + (datePicker.date.getMonth() + 1) + "."+ datePicker.date.getFullYear();
}

options = {
    autoClose: true,
    format: "dd.mm.yyyy",
    onSelect: selectDate,
    defaultDate: Date.now(),
    firstDay: 1
}

elems = document.querySelectorAll('.datepicker');
var datePicker = M.Datepicker.init(elems, options)[0];

function toggleTimePupup(reload) {
    if (reload) {
        location.reload();
    }

    $("#add-time").toggleClass("closed");
}

function openTimePopup(date){
    $("#add-time").removeClass("closed");
    $("#add-time").find("form").attr("action", "/time?date="+date);
    $("#old-date").val(date);
}

function toggleEditTimePupup(reload) {
    if (reload) {
        location.reload();
    }

    $("#edit-time").toggleClass("closed");
}

var hasInterupt = false;
function toggleInterrupt() {
    console.log($(".interrupt-toggle").val());
    if(hasInterupt){
        $(".interrupt-toggle").val(0);
        $(".interrupt").attr("disabled", true);
        $(".interrupt")[0].value = "";
        $(".interrupt")[1].value = "";
        hasInterupt = false;
    } else {
        $(".interrupt-toggle").val(1);
        $(".interrupt").attr("disabled", false);
        $(".interrupt")[0].value = "12:00";
        $(".interrupt")[1].value = "13:00";
        hasInterupt = true;
    }
}

function changeInputType(thisElement){
    if(thisElement.value == 1){
        $(".timepicker-from").attr("readonly", false);
        $(".timepicker-to").attr("readonly", false);
        $(".timepicker-from").val("");
        $(".timepicker-to").val("");
        $(".interrupt-toggle").attr("disabled", false);
        timepickers = M.Timepicker.init(elems, options);
    } else {
        $(".timepicker-from").attr("readonly", "readonly");
        $(".timepicker-to").attr("readonly", "readonly");
        $(".interrupt-toggle").attr("disabled", true);
        timepickers.forEach(timepicker => {
            timepicker.destroy();
        });
        let hours = Math.floor(hoursPerDay);
        let minutes = hoursPerDay - hours;
        hours = 8+hours;
        minutes = 60 / 100 * minutes;
        minutes = minutes.toFixed(2) * 100;
        if(minutes < 10){
            minutes = "0"+minutes;
        }
        if(hours < 10){
            hours = "0"+hours;
        }
        $(".timepicker-from").val("08:00");
        $(".timepicker-to").val(hours + ":" + minutes);
    }
}

function toggleSettingsButtons(thisElement){
    $(thisElement).find(".settings-buttons").toggle();
}

var hourId;
function editHour(id, from, to, worktype, comment, lunch){
    hourId = id;
    var editTimeElement = $("#edit-time");

    editTimeElement.removeClass("closed");

    editTimeElement.find("#workType").val(worktype);
    $('select').formSelect();
    editTimeElement.find("#from").val(from);
    editTimeElement.find("#to").val(to);
    editTimeElement.find("#comment").val(comment);
    editTimeElement.find("#lunch").attr("checked", lunch);

    editTimeElement.find("form").attr("action", "/time/"+id);
    editTimeElement.find("#patch_url").val("/time/" + id);
    M.updateTextFields()
}

function deleteHour(id){
    $.ajax({
        url: "/time/" + id,
        type: 'delete'
    }).done(() => {
        location.reload();
    }).fail(() => {
        swal("Fehler", "Umbekannter Fehler ist aufgetreten!", "error");
    });
}

function showError(){
    $("#add-time").removeClass("animate");
    $("#add-time").removeClass("closed");

    setTimeout(() => {
        $("#add-time").addClass("animate");
    }, 500);
}

function showUpdateError(){
    $("#edit-time").removeClass("animate");
    $("#edit-time").removeClass("closed");

    setTimeout(() => {
        $("#edit-time").addClass("animate");
    }, 500);
}