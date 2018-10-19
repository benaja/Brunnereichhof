$.get("/cultures/all").done(cultures => {
    var autocompleteData = {};

    for(let culture of cultures){
        autocompleteData[culture.name] = null;
    }

    $('input.autocomplete').autocomplete({
        data: autocompleteData
    });
    console.log("test");
});

function showWeekTime(){
    var weekCounter = 0;
    weeks = Array();
    for(var i = 1; i <= 52; i++){
        if($("#"+i+"_check").is(":checked")){
            weeks[weekCounter] = i;
            weekCounter++;
            $("#"+i+"_time").show();
            $("#"+i+"_hours").attr("required", "required");
            $("#"+i+"_culture").attr("required", "required");
        }else{
            $("#"+i+"_time").hide();
        }
    }

    $("#chose_week").hide();
    $("#select_time").show();
}

function goBack(){
    $("#chose_week").show();
    $("#select_time").hide();
}

function save(){
    var validation = true;
    var counter = 0;
    var hours = Array();
    var cultures = Array();
    var comments = Array();
    weeks.forEach(function(week){
        if($("#"+week+"_hours").val() == ""){
            validation = false;
        }else if($("#"+week+"_culture").val() == ""){
            validation = false;
        }else{
            hours[counter] = $("#"+week+"_hours").val();
            cultures[counter] = $("#"+week+"_culture").val();
            comments[counter] = $("#"+week+"_comment").val();
        }
        counter++;

    })

    if(validation){
        $("#save_button").attr('disabled', 'disabled');
        $.ajax({
            url: "/plan",
            type: 'post',
            data: {
                weeks: weeks,
                hours: hours,
                cultures: cultures,
                comments: comments
            }
        }).done(() => {
            swal("Erfolgreich gespeichert", "Deine Eingaben konnten erfolgreich gespeichert werden!", "success")
                .then(function() {
                    window.location.href = "/plan/edit";
                });
        }).fail(() => {
            swal("Fehler", "Unbekannter Fehler ist aufgetreten", "Error");
            $("#save_button").removeAttr('disabled');
        })
    }else{
        swal("Ungültige Eingabe", "Fülle bitte alle Stunden und Kulturen aus!", "info");
    }
}

function saveInputChange(thisElement){
    var inhalt = $(thisElement).val();
    var element = $(thisElement).attr('name');
    var elementId = $(thisElement).parent().parent().attr('id');
    $.ajax({
        url: "/plan/"+elementId,
        type: 'patch',
        data: {
            element: element,
            data: inhalt
        },
        success: function(data){
        },
        error: function(data){
            
        }
    });
}