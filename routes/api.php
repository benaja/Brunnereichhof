<?php
Route::post('auth/login', 'AuthController@login');
Route::post('auth/reset-password', 'AuthController@resetPassword');
Route::post('auth/set-password', 'AuthController@setPassword');
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('auth/user', 'AuthController@user');
});
Route::group(['middleware' => 'jwt.refresh'], function () {
    Route::get('auth/refresh', 'AuthController@refresh');
});

// Customer
Route::patch('customers/{id}/resetpassword', 'CustomerController@resetPassword');
Route::get('customers/{id}/projects', 'CustomerController@projects');
Route::get('customers/{id}/hourrecords', 'HourrecordController@getByCustomer');
Route::resource('customers', 'CustomerController');

// Employee
Route::get('pdf/day-total/employees/{employeeId}', 'EmployeeController@dayTotalsPdf');
Route::get('pdf/reservations/employees/{employeeId}', 'EmployeeController@reservationsPdf');
Route::get('pdf/employees/food/year/{date}', 'EmployeeController@foodRapportByYear');
Route::get('pdf/employees/food/month/{date}', 'EmployeeController@foodRapportByMonth');
Route::get('guests', 'EmployeeController@guests');
Route::patch('guests/{id}', 'EmployeeController@update');
Route::get('employeeswithguests', 'EmployeeController@employeesWithGuests');
Route::delete('employees/{employee}/profileimage', 'EmployeeController@deleteImage');
Route::post('employees/{employee}/profileimage', 'EmployeeController@uploadImage');
Route::resource('employees', 'EmployeeController');

// Worker
Route::resource('workers', 'WorkerController');

//Project
Route::delete('customers/{customerId}/projects/{projectId}', 'ProjectController@removeFromCustomer');
Route::post('customers/{customerId}/projects/{projectId}', 'ProjectController@addToCustomer');
Route::resource('projects', 'ProjectController');

//Rapport
Route::get('pdf/rapports/{rapport}', 'Evaluation\CustomerPdfController@weekRapportByRapportId');
Route::get('rapports/week/{week}', 'RapportController@showWeek');
Route::post('rapports/{rapport}/employees', 'RapportController@addEmployee');
Route::delete('rapports/{rapport}/employee/{employeeId}', 'RapportController@removeEmployee');
Route::patch('rapportdetails/{rapportdetail}', 'RapportController@updateRapportdetail');
Route::patch('rapportdetails', 'RapportController@updateMultibleRapportdetails');
Route::get('rapports/daytotal/{date}', 'RapportController@daytotal');
Route::resource('rapports', 'RapportController');

// Pdfs
Route::get('pdf/timerecords/meals', 'Evaluation\WorkerPdfController@meals');
Route::get('pdf/timerecords/workers/{workerId}', 'Evaluation\WorkerPdfController@timerecords');
Route::get('pdf/rapports/employees/{employeeId}', 'Evaluation\EmployeePdfController@yearRapport');
Route::get('pdf/rapports/employees', 'Evaluation\EmployeePdfController@monthRapport');
Route::get('pdf/employees', 'Evaluation\EmployeePdfController@employeeList');
Route::get('pdf/foods/employees', 'Evaluation\EmployeePdfController@foodRapport');
Route::get('pdf/customers/{customerId}/year/{year}', 'Evaluation\CustomerPdfController@customerYearRapport');
Route::get('pdf/customers/{customerId}/week/{date}', 'Evaluation\CustomerPdfController@weekRapport');
Route::get('pdf/reservations', 'ReservationPdfController@pdfByEmployee');
Route::get('pdf/rooms/{roomId}/reservations', 'RoomController@reservationsPdf');
Route::get('export/customers', 'Evaluation\CustomerPdfController@csvExport');

Route::post('password/change', 'UserController@changePassword');
Route::post('resetpassword/{user}', 'UserController@resetPassword');

Route::get('times/{date}', 'TimeController@index');
Route::get('times/week/{date}', 'TimeController@week');
Route::get('times/stats/{date}', 'TimeController@stats');
Route::resource('/times', 'TimeController');

Route::get('stats/roomdispositioner', 'DashboardController@roomdispositioner');
Route::get('stats', 'DashboardController@allStats');

Route::patch('settings', 'SettingsController@update');
Route::get('settings/time', 'SettingsController@timeSettings');
Route::get('settings', 'SettingsController@index');
Route::get('settings/hourrecords', 'SettingsController@hourrecordSettings');

Route::resource('/cultures', 'CultureController');

Route::get('hourrecords/{year}/{week}', 'HourrecordController@getByWeek');
Route::get('pdf/hourrecords/{year}/customers/{customer}', 'HourrecordController@hourrecordYearRappport');
Route::get('pdf/hourrecords', 'HourrecordController@pdfByWeek');
Route::post('hourrecords/{year}/{week}', 'HourrecordController@createSingle');
Route::patch('hourrecords', 'HourrecordController@updateMultible');
Route::resource('hourrecords', 'HourrecordController');

Route::get('rules', 'RoleController@getRules');
Route::resource('roles', 'RoleController');


// roomdispositioner
Route::get('rooms/evaluation/{date}/pdf', 'RoomController@evaluationPdf');
Route::patch('rooms/{roomId}/beds/{bedId}', 'RoomController@addBed');
Route::delete('rooms/{roomId}/beds/{pivotId}', 'RoomController@removeBed');
Route::get('rooms/{id}/beds', 'RoomController@getBeds');
Route::get('rooms/reservations/{date}', 'RoomController@evaluation');
Route::patch('room/{room}', 'RoomController@update');
Route::post('rooms/{roomId}/images', 'RoomController@uploadImages');
Route::delete('images/{imageId}', 'RoomController@deleteImage');
Route::resource('/rooms', 'RoomController');
Route::get('rooms/{roomId}/reservations', 'RoomController@reservations');
Route::get('pdf/sleep-over/rooms', 'RoomController@sleepOver');

Route::patch('beds/{bedId}/inventars/{inventarId}', 'BedController@addInventar');
Route::delete('beds/{bedId}/inventars/{inventarId}', 'BedController@removeInventar');
Route::resource('beds', 'BedController');

Route::get('stats/quartering', 'ReservationController@quartering');
Route::get('stats/room-changes', 'ReservationController@roomChanges');
Route::resource('/reservations', 'ReservationController');

Route::resource('inventars', 'InventarController');

Route::resource('worktypes', 'WorktypeController');
