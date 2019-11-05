<?php
Route::post('auth/login', 'AuthController@login');
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('auth/user', 'AuthController@user');
    Route::get('/pdftoken', 'AuthController@generatePdfToken');
});
Route::group(['middleware' => 'jwt.refresh'], function () {
    Route::get('auth/refresh', 'AuthController@refresh');
});

// Customer
Route::patch('customer/{id}/resetpassword', 'CustomerController@resetPassword');
Route::get('customer/{id}/projects', 'CustomerController@projects');
Route::resource('customer', 'CustomerController');

// Employee
Route::get('/employee/{employeeId}/evaluation/year/{year}', 'EmployeeController@employeeDayTotalsByYear');
Route::get('/employee/{employeeId}/evaluation/month/{month}', 'EmployeeController@employeeDayTotalsByMonth');
Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/guest', 'EmployeeController@guests');
    Route::patch('/guest/{id}', 'EmployeeController@update');
    Route::get('/employeeswithguests', 'EmployeeController@employeesWithGuests');
    Route::delete('/employee/{employee}/editimage', 'EmployeeController@deleteImage');
    Route::post('/employee/{employee}/editimage', 'EmployeeController@uploadImage');
    Route::resource('employee', 'EmployeeController');
});

// Worker
Route::resource('/worker', 'WorkerController');

//Project
Route::get('/project/exist/{name}', 'ProjectController@exist');
Route::delete('/project/{projectId}/customer/{id}', 'ProjectController@removeFromCustomer');
Route::post('/project/add', 'ProjectController@addToCustomer');
Route::resource('/project', 'ProjectController');

//Rapport
// Route::get('/rapport/choosecustomer', 'RapportController@chooseCustomer');
// Route::get('/rapport/addcustomer/{customer}', 'RapportController@addCustomer');
// Route::get('/rapport/show', 'RapportController@showAll');
// Route::post('/rapport/convertdate', 'RapportController@convertDate');
Route::get('/rapport/week/{week}', 'RapportController@showWeek');
Route::get('/rapport/{rapport}/pdf', 'Evaluation\CustomerPdfController@weekRapportByRapportId');
Route::post('/rapport/{rapport}/employee', 'RapportController@addEmployee');
Route::delete('/rapport/{rapport}/employee/{employeeId}', 'RapportController@removeEmployee');
Route::patch('/rapportdetail/{rapportdetail}', 'RapportController@updateRapportdetail');
Route::patch('/rapportdetails', 'RapportController@updateMultibleRapportdetails');
Route::get('/rapport/daytotal/{date}', 'RapportController@daytotal');
Route::resource('/rapport', 'RapportController');

Route::get('/pdf/worker/month/{month}', 'PdfController@workerMonthRapport');
Route::get('/pdf/employee/year/{year}', 'PdfController@employeeYearRapport');
Route::get('/pdf/employee/month/{month}', 'PdfController@employeeMonthRapport');
Route::get('/pdf/employees', 'PdfController@employeeList');
Route::get('/pdf/customer/year/{year}', 'PdfController@customerYearRapport');
Route::get('/pdf/customer/week/{date}', 'Evaluation\CustomerPdfController@weekRapport');
Route::get('/pdf/reservation/employee/{id}', 'ReservationPdfController@pdfByEmployee');

Route::post('/password/change', 'UserController@changePassword');
Route::post('/resetpassword/{user}', 'UserController@resetPassword');

Route::get('/time/{date}', 'TimeController@index');
Route::get('/time/week/{date}', 'TimeController@week');
Route::get('/time/stats/{date}', 'TimeController@stats');
Route::resource('/time', 'TimeController');

Route::get('/stats/roomdispositioner', 'DashboardController@roomdispositioner');
Route::get('/stats', 'DashboardController@allStats');

Route::patch('/settings', 'SettingsController@update');
Route::get('/settings/time', 'SettingsController@timeSettings');
Route::get('/settings', 'SettingsController@index');
Route::get('/settings/hourrecords', 'SettingsController@hourrecordSettings');

Route::resource('/culture', 'CultureController');

Route::get('/hourrecord/{year}/{week}', 'HourrecordController@getByWeek');
Route::post('/hourrecord/week/{week}', 'HourrecordController@createSingle');
Route::resource('/hourrecord', 'HourrecordController');

Route::get('rules', 'RoleController@getRules');
Route::resource('roles', 'RoleController');


// roomdispositioner
Route::get('/rooms/evaluation/{date}/pdf', 'RoomController@evaluationPdf');
Route::middleware(['jwt.auth'])->group(function () {
    Route::patch('/rooms/{roomId}/beds/{bedId}', 'RoomController@addBed');
    Route::delete('/rooms/{roomId}/beds/{pivotId}', 'RoomController@removeBed');
    Route::get('/rooms/{id}/beds', 'RoomController@getBeds');
    Route::get('/rooms/reservations/{date}', 'RoomController@evaluation');
    Route::resource('/rooms', 'RoomController');
});

Route::patch('/beds/{bedId}/inventars/{inventarId}', 'BedController@addInventar');
Route::delete('/beds/{bedId}/inventars/{inventarId}', 'BedController@removeInventar');
Route::resource('/beds', 'BedController');

Route::resource('/reservations', 'ReservationController');

Route::resource('/inventars', 'InventarController');
