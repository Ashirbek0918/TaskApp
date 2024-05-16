<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ObligationController;

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

Route::post('login',[AuthController::class,'login']);
Route::middleware('auth:sanctum')->group(function(){
    Route::post('admin/create',[AuthController::class,'create'])->middleware('can:obligation-control');
    Route::put('admin/update/{user}',[AuthController::class,'update']);
    Route::delete('admin/delete/{user}',[AuthController::class,'delete'])->middleware('can:obligation-control');
    Route::get('admins',[AuthController::class,'all']);
    Route::get('getme',[AuthController::class,'getme']);
    Route::delete('logOut',[AuthController::class,'logOut']);

    //Task
    Route::post('task/add',[TaskController::class,'addTask']); 
    Route::get('task/{task}',[TaskController::class,'task']); 
    Route::get('tasks',[TaskController::class,'all']);
    Route::get('mytasks/{id}',[TaskController::class,'mytasks']);
    Route::delete('task/delete/{task}',[TaskController::class,'delete'])->middleware('can:delete-task,task');
    Route::put('task/update/{task}',[TaskController::class,'update']);
    //message

    Route::post('message/add',[MessageController::class,'add']);
    Route::get('messages',[MessageController::class,'all']);
    Route::get('message/{message}',[MessageController::class,'message']);
    Route::post('message/read',[MessageController::class,'read']);
    Route::put('message/update/{message}',[MessageController::class,'update'])->middleware('can:update-message,message');
    Route::delete('message/delete/{message}',[MessageController::class,'delete'])->middleware('can:delete-message,message');
    Route::get('obligations',[ObligationController::class,'all']);

    //obligation
    Route::middleware('can:obligation-control')->group(function(){
        Route::post('obligation/create',[ObligationController::class,'create']);
        Route::delete('obligation/delete/{obligation}',[ObligationController::class,'delete']);
        Route::put('obligation/update/{obligation}',[ObligationController::class,'edit']);

    });

    Route::get('allcount',[MessageController::class,'alldata']);
});
