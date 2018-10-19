$(document).ready(function(){
    $('.login').dropdown({
        alignment: "right"
    });
    $(".user-administation-dropdown").dropdown();
    $('.collapsible').collapsible();

    var elem = document.querySelector('.sidenav');
    var sidenav = M.Sidenav.init(elem);    

    $("#menu-button").click(function(){
        sidenav.open();
    });
});

function deleteItem(link, id, name) {
    swal({
        title: 'Bist du sicher?',
        text: name + ' wird unwiederruflich gelöscht!',
        type: 'warning',
        buttons: ['Nein', 'Ja'],
    }).then(result => {
        if (result) {
            $.ajax({
                url: link + id,
                type: 'delete'
            }).done(() => {
                swal({
                    title:'Gelöscht!',
                    text: name + ' wurde gelöscht.',
                    type: 'success',
                    allowOutsiteClick: false
                }).then(function(){
                    location.href=link;
                });
            });
        }
    });
}

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

class PropertyEditor{
    constructor(saveUrl, elementId) {
        this.saveUrl = saveUrl;
        this.elementId = elementId;

        $(".info_content").dblclick(event => {
            this.convertToInputField(event.currentTarget);
        });

        $(".filled-in, .input-field select").change(event => {
            this.saveInputChange(event.currentTarget);
        });

        if(screen.width < 480){
            $(".info_content").click(event => {
                this.convertToInputField(event.currentTarget);
            });
        }
    }

    convertToInputField(thisElement){
        let inhalt = $(thisElement).html();
        let id = $(thisElement).attr('id');
        let inputElement = document.createElement("input");
        inputElement.setAttribute('class', 'col s8 edit_input');
        inputElement.setAttribute('name', id);

        inputElement.addEventListener("blur", () => this.saveInputChange(inputElement));

        if(id == 'culture'){
            inputElement.setAttribute('class','col s8 edit_input autocomplete');
        }

        if($(thisElement).hasClass("number")){
            inputElement.setAttribute('type', 'number');    
        }
        inputElement.addEventListener("focusout", event => this.focusOut(event.currentTarget));
        
        inputElement.value = inhalt;
        $(thisElement).parent().append(inputElement);
        $(thisElement).hide();
        window.getSelection().removeAllRanges();
        inputElement.focus();
    }

    saveInputChange(thisElement) {
        let inhalt = $(thisElement).val();
        let element = $(thisElement).attr('name');

        if($(thisElement).hasClass("filled-in")){
            inhalt = $(thisElement).is(':checked') ? 1 : 0;
        }
        
        let textElement = thisElement.parentElement.getElementsByClassName("info_content")[0];
        let saveId = this.elementId;
        if(textElement && textElement.dataset.saveid){
            saveId = textElement.dataset.saveid;
        }
        $.ajax({
            url: `/${this.saveUrl}/` + saveId,
            type: 'patch',
            data: {
                element: element,
                data: inhalt
            }
        });
    }

    focusOut(thisElement){
        let inhalt = $(thisElement).val();
        let textElement = $(thisElement).parent().find(".info_content");
        textElement.html(inhalt);

        $(thisElement).remove();
        if (inhalt != "") {
            textElement.removeClass('empty');
        } else {
            textElement.addClass('empty');
        }
        textElement.show();
    }
}