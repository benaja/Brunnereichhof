$(document).ready(function () {
    $('.collapsible').collapsible();

    var workerService = new WorkerService();
    var employeeService = new EmployeeService();
    var customerService = new CustomerService();
});

const monthNames = ["January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

class WorkerService{
    constructor(){
        this.allWorkers = {};
        this.getAllWorkers();
    }

    getAllWorkers(){
        $.get("/worker/all")
        .done(data => {
            data.forEach(worker => {
                this.allWorkers[worker.firstname + " " + worker.lastname] = null;
            });

            this.hourRecordsSelector = new Selector("#pick_month_worker", "month", "#button_month_worker", "/overview/worker/month/", "#search_worker", this.allWorkers);
        });
    }
}

class EmployeeService{
    constructor(){
        this.allEmployees = {};
        this.getAllEmployees();
        this.initiateMonthRapport();
    }

    getAllEmployees(){
        $.get('/employee/all')
        .done(data => {
            data.forEach(employee => {
                this.allEmployees[employee.firstname + " " + employee.lastname] = null;
            });

            this.yearRapportSelector = new Selector("#pick_year_employee", "year", "#button_year_employee", "/overview/employee/year/", "#search_employee", this.allEmployees);
        });
    }

    initiateMonthRapport(){
        this.monthRapportSelector = new Selector("#pick_month_employee", "month", "#button_month_employee", "/overview/employee/month/");
    }
}

class CustomerService{
    constructor(){
        this.allCustomers = {};
        this.getAllCustomers();
    }

    getAllCustomers(){
        $.get('/customer/all')
        .done(data => {
            data.forEach(customer => {
                this.allCustomers[customer.firstname + " " + customer.lastname] = null;
            });

            this.yearRapportSelector = new Selector("#pick_year_customer", "year", "#button_customer_year", "/overview/customer/year/", "#search_customer", this.allCustomers);
        });
    }
}

class Selector{
    constructor(datePickerElement, view, buttonElement, link, searchElement = null, allPersons = null) {
        this.view = view;
        this.link = link;
        this.allPersons = allPersons;
        this.buttonElement = buttonElement;
        this.searchElement = searchElement;
        this.datePickerElement = datePickerElement;
        this.datePicker =  TinyDatePicker(datePickerElement);
        
        this.initiateDatePicker();

        if(this.searchElement != null){
            this.initiateAutoComplete();
        }else{
            this.isPersonSelected = true;
        }
    }

    initiateAutoComplete(){
        let options = {
            data: this.allPersons,
            onAutocomplete: () => this.updatePerson()
        };

        let elems = document.querySelectorAll(this.searchElement);
        this.autoCompleteInstance = M.Autocomplete.init(elems, options);

        $(this.searchElement).on("blur", () => this.checkIfPersonExist());
    }

    initiateDatePicker(){
        this.datePicker.on('open', () => {
            this.datePicker.setState({
                view: this.view,
            });
        });

        if(this.view != "day"){
            this.datePicker.on('statechange', (_, picker) => {
                if(this.view == "month" && picker.state.view == "day"){
                    $(this.datePickerElement).val(monthNames[picker.state.hilightedDate.getMonth()]);
                    picker.close();
                    M.updateTextFields();
                    this.isDateSelected = true;
                    this.toggleButton();
                }else if(picker.state.view == "day"){
                    $(this.datePickerElement).val(picker.state.hilightedDate.getFullYear());
                    picker.close(); 
                    M.updateTextFields();
                    this.isDateSelected = true;
                    this.toggleButton();
                }
            });
        }
    }

    updatePerson(){
        this.isPersonSelected = true;
        this.toggleButton();
    }

    checkIfPersonExist(){
        let name = $(this.searchElement).val();
        if (name in this.allPersons){
            this.isPersonSelected = true;
            this.toggleButton();
        }else{
            this.isPersonSelected = false;
            this.toggleButton();
        }
    }

    toggleButton(){  
        if(this.isDateSelected && this.isPersonSelected){
            $(this.buttonElement).removeClass("disabled");
            let link = this.link+$(this.datePickerElement).val()+"?name="+$(this.searchElement).val();
            $(this.buttonElement).attr("href", link);
            
        }else{
            $(this.buttonElement).addClass("disabled");
        }
    }
}