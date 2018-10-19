$(document).ready(function(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/cultures",
        type: 'get',
        success: function(data){
            var cultures = JSON.parse(data);
            
            
            for(var i = 0; i < Object.keys(cultures).length; i++){
                //console.log(cultures[i].name);
                autocompleteData[cultures[i].name] = null;
            }

            $('input.autocomplete').autocomplete({
                data: autocompleteData
            });
        },
        error: function(data){
            
        }
    });
});
function showWeekTime(){
    var weekCounter = 0;
    weeks = Array();
    for(var i = 1; i <= 52; i++){
        if($("#"+i+"_check").is(":checked")){
            weeks[weekCounter] = i;
            weekCounter++;
            $("#week_"+i).show();
            $("#"+i+"_hours").attr("required", "required");
            $("#"+i+"_culture").attr("required", "required");
        }else{
            $("#week_"+i).hide();
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
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/plan",
            type: 'post',
            data: {
                weeks: weeks,
                hours: hours,
                cultures: cultures,
                comments: comments
            },
            success: function(data){
                swal({
                    type: 'success',
                    title: 'Erfolgreich gespeichert',
                    text: 'Deine Eingaben konnten erfolgreich gespeichert werden!',
                    //allowOutsideClick: false,
                }).then(function(){
                    window.location.href = "/plan/edit";
                });
            },
            error: function(data){
                swal({
                    type: 'error',
                    title: 'Fehler',
                    text: 'Unbekannter Fehler ist aufgetreten!'
                })
                $("#save_button").removeAttr('disabled');
            }
        });
    }else{
        swal({
            type: 'error',
            title: 'Ungültige Eingabe',
            text: 'Fülle bitte alle Stunden und Kulturen aus!'
        })
    }
}

function saveInputChange(thisElement){
    var inhalt = $(thisElement).val();
    var element = $(thisElement).attr('name');
    var elementId = $(thisElement).parent().parent().attr('id');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
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