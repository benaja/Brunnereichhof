<?php

use Illuminate\Http\Request;

Route::post('auth/login', 'AuthController@login');
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('auth/user', 'AuthController@user');
    
});
Route::group(['middleware' => 'jwt.refresh'], function () {
    Route::get('auth/refresh', 'AuthController@refresh');
});

// Customer
Route::patch('customer/{id}/resetpassword', 'CustomerController@resetPassword');
Route::resource('customer', 'CustomerController');

// Employee
Route::delete('/employee/{employee}/editimage', 'EmployeeController@deleteImage');
Route::post('/employee/{employee}/editimage', 'EmployeeController@uploadImage');
Route::resource('employee', 'EmployeeController');

// Worker
Route::resource('/worker', 'WorkerController');

//Project
Route::get('/project/customer/{customer}', 'ProjectController@allByCustomer');
Route::get('/project/exist/{name}', 'ProjectController@exist');
Route::delete('/project/{projectName}/customer/{customer}', 'ProjectController@removeFromCustomer');
Route::post('/project/add', 'ProjectController@addToCustomer');
Route::resource('/project', 'ProjectController');

