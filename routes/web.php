<?php

Auth::routes();

Route::get('/', 'HomeController@index')->name('test');
Route::get('/home', 'HomeController@home')->name('home');
Route::get('/profile/edit', 'HomeController@editProfil');
Route::post('/password/change', 'HomeController@changePassword');
Route::get('/password/changed', 'HomeController@passwordChanged');

Route::get('/customer/find', 'CustomerController@find');
Route::get('customer/all', 'CustomerController@all');
Route::resource('/customer', 'CustomerController');

Route::get('/project/customer/{customer}', 'ProjectController@allByCustomer');
Route::get('/project/exist/{name}', 'ProjectController@exist');
Route::delete('/project/{projectName}/customer/{customer}', 'ProjectController@removeFromCustomer');
Route::post('/project/add', 'ProjectController@addToCustomer');
Route::resource('/project', 'ProjectController');


Route::get('/plan', 'PlanController@index');
Route::post('/plan', 'PlanController@store');
Route::get('/cultures/all   ', 'PlanController@cultures');
Route::get('/plan/edit', 'PlanController@edit');
Route::patch('/plan/{id}', 'PlanController@update');
Route::get('/plan/delete/{id}', 'PlanController@delete');


Route::get('/evaluation', 'EvaluationController@index');
Route::get('/evaluation/weeks', 'EvaluationController@weeks');
Route::get('/evaluation/week/{id}', 'EvaluationController@week');

Route::get('/employee/all', 'EmployeeController@all');
Route::post('/employee/{employee}/editimage', 'EmployeeController@update');
Route::resource('/employee', 'EmployeeController');

Route::get('/rapport/choosecustomer', 'RapportController@chooseCustomer');
Route::get('/rapport/addcustomer/{customer}', 'RapportController@addCustomer');
Route::get('/rapport/show', 'RapportController@showAll');
Route::post('/rapport/convertdate', 'RapportController@convertDate');
Route::get('/rapport/week/{week}', 'RapportController@showWeek');
Route::get('/rapport/{rapport}/pdf', 'RapportController@generatePdf');
Route::resource('/rapport', 'RapportController');


Route::get('/overview/employee/year/{year}', 'OverviewController@generateEmployeYearOverview');
Route::get('/overview', 'OverviewController@index');
Route::get('/overview/employee/month/{month}', 'OverviewController@employeeMonthRapport');
Route::get('/overview/customer/year/{year}', 'OverviewController@customerYearRapport');
Route::get('/overview/employees', 'OverviewController@generateEmployeeList');
Route::get('/overview/worker/month/{month}', 'OverviewController@workerMonthRapport');

Route::get('/worker/all', 'WorkerController@all');
Route::resource('/worker', 'WorkerController');

Route::resource('/time', 'TimeController');