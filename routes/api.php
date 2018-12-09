<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::patch('customer/{id}/resetpassword', 'CustomerController@resetPassword');
Route::resource('customer', 'CustomerController');

Route::delete('/employee/{employee}/editimage', 'EmployeeController@deleteImage');
Route::post('/employee/{employee}/editimage', 'EmployeeController@uploadImage');
Route::resource('employee', 'EmployeeController');

//Project
Route::get('/project/customer/{customer}', 'ProjectController@allByCustomer');
Route::get('/project/exist/{name}', 'ProjectController@exist');
Route::delete('/project/{projectName}/customer/{customer}', 'ProjectController@removeFromCustomer');
Route::post('/project/add', 'ProjectController@addToCustomer');
Route::resource('/project', 'ProjectController');

