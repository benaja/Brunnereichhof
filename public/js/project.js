class ProjectSelector{
    constructor(){
        $.get("/project/customer/" + customerId).done(response => {
            this.autocompleteData = {};
            response.allProjects.forEach(project => {
                this.autocompleteData[project.name] = null;
            });

            this.customerProjects = [];
            response.customerProjects.forEach(project => {
                this.customerProjects.push({
                    tag: project.name,
                    id: project.id
                });
            });

            let options = {
                placeholder: "Projekt hinzufügen",
                secondaryPlaceholder: "Projekt hinzufügen",
                onChipAdd: () => this.chipAdded(),
                onChipSelect: (init, selectedElement) => this.chipSelected(init, selectedElement),
                onChipDelete: (init, selectedElement) => this.chipDeleted(init, selectedElement),
                autocompleteOptions: {
                    data: this.autocompleteData
                },
                data: this.customerProjects
            }

            let elems = document.querySelectorAll('.chips')[0];
            this.chipsInstance = M.Chips.init(elems, options);
        });
    }

    chipAdded(){
        let addedProjectname = this.chipsInstance.chipsData[this.chipsInstance.chipsData.length - 1].tag;
        $.get("/project/exist/" + addedProjectname).done(exist => {
            if (exist == 1) {
                $.ajax({
                    url: "/project/add",
                    type: 'post',
                    data: {
                        title: addedProjectname,
                        customerId: customerId
                    }
                });
            } else {
                swal({
                    title: '"' + addedProjectname + '" existiert noch nicht!',
                    text: "Wollen sie ein neues Projekt erstellen",
                    buttons: {
                        cancel: "Nein",
                        confirm: "Ja"
                    }
                }).then((result) => {
                    if (result) {
                        setTimeout(moveOne, 100);
                        function moveOne() {
                            swal({
                                title: "Geben sie doch eine Beschreibung ein!",
                                content: {
                                    element: "input",
                                    attributes: {
                                        placeholder: "Beschreibung"
                                    }
                                },
                                buttons: {
                                    confirm: "Erstellen"
                                },
                                closeOnClickOutside: false
                            }).then((description) => {
                                $.ajax({
                                    url: "/project",
                                    type: 'post',
                                    data: {
                                        title: addedProjectname,
                                        description: description,
                                        customerId: customerId
                                    }}).done(() => {
                                        return true;
                                    }).fail(() => {
                                        swal("Erstellung fehlgeschlagen!", "Etwas ist schief gelaufen!", "error");
                                        this.chipsInstance.deleteChip(this.chipsInstance.chipsData.length - 1);
                                    });
                            }).then(() => {
                                swal({
                                    title: 'Projekt erfolgreich erstellt!',
                                    icon: "success"
                                });
                            });
                        }
                    } else {
                        this.chipsInstance.deleteChip(this.chipsInstance.chipsData.length - 1);
                    }
                });
            }
        });
    }

    chipSelected(init, selectedElement) {
        var textContent = selectedElement.textContent;
        var chipName = textContent.substring(0, textContent.length - 5);
    }

    chipDeleted(init, selectedElement) {
         var textContent = selectedElement.textContent;
         var chipName = textContent.substring(0, textContent.length - 5);

         $.ajax({
             url: "/project/" + chipName + "/customer/" + customerId,
             type: 'delete'
         });
    }
}

new ProjectSelector();