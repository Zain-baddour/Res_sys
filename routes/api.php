<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\HallDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OfficeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StripeController;

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

Route::get('/halls', [HallController::class, 'index']);  // Get all halls
Route::get('/halls/{id}/reviews', [HallController::class, 'getHallReviews']);  //Get Hall Reviews
Route::get('/halls/{id}', [HallController::class, 'show']);    // Get single hall
Route::get('/halls/{id}/images', [HallController::class, 'getHallImagesC']);    // Get single hall images
Route::get('showserv/{id}', [HallController::class, 'showservice']); // show a hall services
Route::get('/halls/eventImages/{id}', [HallController::class, 'getEventImages']); // show event images
Route::get('/halls/eventVideos/{id}', [HallController::class, 'getEventVideos']); // show event videos
Route::get('polices/{id}', [HallController::class, 'showpolices']);// show hall policies
Route::get('hallPriceCards/{id}', [HallController::class , 'getHallPrice']); // show price card



Route::middleware(['auth:sanctum','blocked'])->prefix('halls')->group(function () {
    Route::post('/', [HallController::class, 'store']);      // Create a hall
    Route::put('/{id}', [HallController::class, 'update']);  // Update a hall
    Route::delete('/{id}', [HallController::class, 'destroy']); // Delete a hall
    Route::get('/{hallId}/inquiries', [HallController::class, 'getHallInquiries']); //get this hall inquiries
    Route::get('/{hallId}/employees', [HallController::class, 'getHallEmployees']); //get this hall employees with there info
    Route::delete('/employee/{employeeId}', [HallController::class, 'delHallEmployees']); //delete an employee by id
});


// ***** Admin APIs *****

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('/settings', [AdminController::class, 'showSettings']); //show the app settings
    Route::put('/settings/update', [AdminController::class, 'updateSettings']); //update the settings
    Route::get('/settings/office', [AdminController::class, 'showOfficeSettings']); //show the app settings for office
    Route::put('/settings/office/update', [AdminController::class, 'updateOfficeSettings']); //update the settings for office
    Route::get('/pending', [AdminController::class, 'getPendingHalls']);    // get pending halls
    Route::post('/status/{id}', [AdminController::class, 'updateHallStatus']);    // update status from pending to approved or rejected
    Route::get('/allUsers', [AdminController::class, 'getAllUsers']); // get all users
    Route::get('/User/{id}', [AdminController::class, 'getUserById']); // get a user by id
    Route::delete('delete/{id}', [AdminController::class, 'deleteUser']); //delete a user
    Route::post('{id}/block', [AdminController::class, 'blockUser']); //block a user
    Route::post('{id}/unblock', [AdminController::class, 'unblockUser']); //unblock a user
    Route::get('blocked', [AdminController::class, 'blockedUsers']); // show blocked users

});


// ***** Owner APIs *****
Route::middleware(['auth:sanctum','blocked'])->prefix('owner')->group(function () {
    Route::get('/myhall', [OwnerController::class, 'showMyHall']);    // get the owner hall
    Route::get('/getStaffReqs', [OwnerController::class, 'getStaffReqs']);  //get staff requests
    Route::post('/staffReqs/{id}', [OwnerController::class, 'updateStaffReqStatus']); //approve or reject an assistant
});

// ***** Assistant APIs *****
Route::middleware(['auth:sanctum','blocked'])->prefix('assistant')->group(function () {
    Route::post('/inquiry/response', [AssistantController::class, 'responseToInquiry']); //response to an inquiry
    Route::get('/myInquiries/{hall_id?}/{userId}', [ClientController::class, 'myInquiries']);
    Route::post('/requestStaff/{id}', [AssistantController::class, 'requestStaff']); //request to get hired at a hall
    Route::get('getMyStaffRequests' ,[AssistantController::class , 'getStaffRequest']); // get my hall applications
    Route::get('/chats', [AssistantController::class, 'getChat']); //get all chats
    Route::post('addserv/{id}', [HallController::class, 'add_service']); // add a service
    Route::post('updateservice/{id}', [HallController::class, 'updatservice']); // update a service
    Route::get('/hallBookings', [BookingController::class, 'getHallBookings']);// get all bookings to the assistant hall
    Route::get('/hallConfirmedBookings', [BookingController::class, 'getHallConfirmedBookings']);// get all Confirmed bookings to the assistant hall
    Route::post('/updateDetail/{id}', [HallController::class, 'add_detail']); // update hall details
    Route::post('/uploadImages', [AssistantController::class , 'uploadEventImages']);// upload images
    Route::post('/uploadVideos', [AssistantController::class , 'uploadEventVideos']);// upload videos
    Route::post('addpolices/{id}', [HallController::class, 'addpolices']); //add hall policies
    Route::post('updatepolic/{id}', [HallController::class, 'updatepolices']); //update hall policies
    Route::post('addPrice/{id}', [HallController::class , 'addPrice']); // add price card
    Route::put('updatePrice/{id}', [HallController::class , 'updatePrice']); // update price card



    Route::post('addpay/{id}', [HallController::class, 'add_pay']);
    Route::post('updatepay/{id}', [HallController::class, 'updatpay']);
    Route::get('/showpay/{id}', [HallController::class, 'showPayWay']);



});

// ***** Client APIs *****
Route::middleware(['auth:sanctum','blocked'])->prefix('Client')->group(function () {
    Route::post('/inquiry', [ClientController::class, 'store']); //send an inquiry
    Route::get('/myInquiries/{hall_id}', [ClientController::class, 'myInquiries']); //get client inquiries
    Route::post('/reviews', [ClientController::class, 'storeReview']); //review and comment on a hall
    Route::get('/myBookings', [ClientController::class, 'getMyBook']); // get all client's bookings
});

// ***** Booking APIs *****
Route::middleware(['auth:sanctum','blocked'])->prefix('Booking')->group(function () {

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

// ***** Stripe APIs *****
Route::middleware('auth:sanctum')->post('/stripe/hall-subscription', [StripeController::class, 'createSubscriptionPayment']);
Route::middleware(['auth:sanctum','role:admin'])->get('stripe/getPayments', [StripeController::class, 'listPayments']);

//Route::post('/stripe/payment-intent', [StripeController::class, 'createPaymentIntent']);

// ***** Owner Dashboard APIs *****
Route::middleware('auth:sanctum')->get('/Owner-dashboard/statistics', [HallDashboardController::class, 'getStatistics']);

// ***** Notification APIs *****
Route::middleware('auth:sanctum')->get('/notifications', [NotificationController::class, 'index']);

//*********************
//ZainHassan ********
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('addoffer/{id}', [HallController::class, 'addoffer']);
    Route::post('updateoffer/{id}', [HallController::class, 'updateoffer']);
    Route::get('offer/{id}', [HallController::class, 'showoffer']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('add_det/{id}', [HallController::class, 'add_detail']);
    Route::get('det/{id}', [HallController::class, 'showdetail']);
    Route::post('updatedet/{id}', [HallController::class, 'updatdet']);
    Route::post('addtime/{id}', [HallController::class, 'add_time']);
    Route::post('updatetime/{id}', [HallController::class, 'updattime']);


});
Route::get('showtime/{id}', [HallController::class, 'showtime']);

// ***** Office APIs *****
Route::middleware(['auth:sanctum'])->prefix('office')->group(function () {
    Route::post('/', [OfficeController::class, 'addoffice']);      // Create an Office
    Route::post('/service/{id}', [OfficeController::class, 'addserv']);      // Create an Office
    Route::post('/addreq/{id}', [OfficeController::class, 'addReqReservation']); //add req
    Route::get('/show', [OfficeController::class, 'showReqReservation']);
    Route::get('/show/{id}', [OfficeController::class, 'get_detail']); //show detail request of booking
    Route::get('showserv', [OfficeController::class, 'showservice']);//show service office
    Route::post('/addcont/{id}', [OfficeController::class, 'add_info_contact']);
    Route::post('/send/{det_id}/{user_id}/{officeId}', [OfficeController::class, 'send_answer']); //send response to bookings user
    Route::get('/getanswer/{user_id}', [OfficeController::class, 'getAnswer']); //get answer to user
    Route::get('/showoffice', [OfficeController::class, 'showoffice']);//get all office
    Route::get('/detailoffice/{off_id}', [OfficeController::class, 'showDetailOffice']);//get all office

});
