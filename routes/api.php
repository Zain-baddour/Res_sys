<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//***** Auth API's *****

Route::controller(AuthController::class)->group(function (){
    Route::post('register', 'register')
        ->name('user.register');

    Route::post('login', 'login')
        ->name('user.login');

    Route::group(['middleware' => ['auth:sanctum']], function (){
        Route::get('logout', 'logout')
            ->name('user.logout');
    });

});

// *****  Hall API's *****

Route::get('/halls', [HallController::class, 'index']);       // Get all halls
Route::get('/halls/{id}', [HallController::class, 'show']);    // Get single hall
Route::get('/halls/{id}/images', [HallController::class, 'getHallImagesC']);    // Get single hall images

Route::middleware(['auth:sanctum'])->prefix('halls')->group(function () {
    Route::post('/', [HallController::class, 'store']);      // Create a hall
    Route::put('/{id}', [HallController::class, 'update']);  // Update a hall
    Route::delete('/{id}', [HallController::class, 'destroy']); // Delete a hall
    Route::get('/{hallId}/inquiries', [HallController::class, 'getHallInquiries']); //get this hall inquiries
});


// ***** Admin APIs *****

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('/pending', [AdminController::class, 'getPendingHalls']);    // get pending halls
    Route::post('/status/{id}', [AdminController::class, 'updateHallStatus']);    // update status from pending to approved or rejected
});


// ***** Owner APIs *****
Route::middleware(['auth:sanctum'])->prefix('owner')->group(function () {
    Route::get('/myhall', [OwnerController::class, 'showMyHall']);    // get the owner hall
    Route::get('/getStaffReqs', [OwnerController::class, 'getStaffReqs']);  //get staff requests
    Route::post('/staffReqs/{id}', [OwnerController::class, 'updateStaffReqStatus']); //approve or reject an assistant
});

// ***** Assistant APIs *****
Route::middleware(['auth:sanctum'])->prefix('assistant')->group(function () {
    Route::post('/inquiry/response', [AssistantController::class, 'responseToInquiry']); //response to an inquiry
    Route::post('/requestStaff/{id}', [AssistantController::class, 'requestStaff']); //request to get hired at a hall
});

// ***** Client APIs *****
Route::middleware(['auth:sanctum'])->prefix('Client')->group(function () {
    Route::post('/inquiry', [ClientController::class, 'store']); //send an inquiry
    Route::get('/myInquiries', [ClientController::class, 'myInquiries']); //get client inquiries
});

// ***** Booking APIs *****
Route::middleware(['auth:sanctum'])->prefix('Booking')->group(function () {

    // حجز صالة
    Route::post('/bookings', [BookingController::class, 'create']);

    // تأكيد الحجز
    Route::post('/bookings/{id}/confirm', [BookingController::class, 'confirm']);

    // تعديل الحجز
    Route::post('/bookings/{id}', [BookingController::class, 'update']);

    // طلب حذف الحجز
    Route::post('/bookings/{id}/request-delete', [BookingController::class, 'delete']);

    // تأكيد حذف الحجز بعد الموافقة على الغرامة
    Route::post('/bookings/{id}/confirm-delete', [BookingController::class, 'delete']);

    // تأكيد دفع الغرامة
    Route::post('/payments/{id}/confirmPenalty', [PaymentController::class, 'confirmPenaltyPayment']);

    // تأكيد دفع حجز
    Route::post('/payments/{paymentId}/confirm', [PaymentController::class, 'confirmPayment']);


});

//*********************
//ZainHassan ********
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('addpolices', [HallController::class, 'addpolices']);
    Route::post('addoffer', [HallController::class, 'addoffer']);
    Route::put('updatepolic/{id}', [HallController::class, 'updatepolices']);
    Route::get('/polices/{id}', [HallController::class, 'showpolices']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('add_det', [HallController::class, 'add_detail']);
    Route::get('/det/{id}', [HallController::class, 'showdetail']);
    Route::put('/updatedet/{id}', [HallController::class, 'updatdet']);
});
