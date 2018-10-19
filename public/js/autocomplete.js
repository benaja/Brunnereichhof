class Autocomplete{
    constructor(name, url = name, urlParams = "") {
        this.url = url;
        this.name = name;
        this.allPersons = {};
        this.allPersonsId = {};
        this.urlParams = urlParams;

        $.get(`/${name}/all`).done(persons => {
            persons.forEach(person => {
                this.allPersons[person.firstname + " " + person.lastname] = null;
                this.allPersonsId[person.firstname + " " + person.lastname] = person.id;
            });

            let options = {
                data: this.allPersons
            };

            let elems = document.querySelectorAll('.autocomplete');
            this.autoCompleteInstance = M.Autocomplete.init(elems, options);

            elems[0].addEventListener("change", evet => this.searchPerson(event.currentTarget))
        });
    }

    searchPerson(element) {
        if (element.value in this.allPersons) {
            window.location = `/${this.url}/${this.allPersonsId[element.value]}${this.urlParams}`;
        }
    }
}