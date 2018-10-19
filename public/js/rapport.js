const   selectMonthEmployee =  TinyDatePicker('#select_date');

function selectDate(){
    selectMonthEmployee.open();
}

selectMonthEmployee.on('select', (_, picker) => {
    var selectedDate = selectMonthEmployee.state.selectedDate;

    var date = selectedDate.getDate() + "."
        + (selectedDate.getMonth()+1) + "."
        + selectedDate.getFullYear();

    window.location="/rapport/choosecustomer?date="+date;
});
