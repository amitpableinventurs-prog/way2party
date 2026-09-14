<?php

use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenseController;
use GuzzleHttp\Middleware;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\WalletController;
use App\Models\AppUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\MessageController;

Route::group(['middleware' => ['mode', 'XSS']], function () {

    Route::get('/', [FrontendController::class, 'home'])->name('home');
    Route::post('/send-to-admin', [FrontendController::class, 'sentMessageToAdmin']);
    Route::get('/privacy_policy', [FrontendController::class, 'privacypolicy']);
    Route::get('/FAQ', [FaqController::class, 'show']);
    Route::get('/appuser-privacy-policy', [FrontendController::class, 'appuserPrivacyPolicyShow']);
    Route::get('/show-details/{id}', [OrderController::class, 'showTicket']);
    Route::get('/events_details/{id}', [EventController::class, 'show']);
    Route::get('organizer/VerificationConfirm/{id}', [FrontendController::class, 'LoginByMailOrganizer']);
    Route::get('organizer/otp-verify/{userid}', [FrontendController::class, 'otpViewOrganizer']);
    Route::post('organizer/otp-verify', [FrontendController::class, 'otpVerifyOrganizer']);

    Route::get('/login/google', [FrontendController::class, 'redirectToGoogleForSignin']);
    Route::get('/login/google/callback', [FrontendController::class, 'loginWithGoogleCallback']);

    Route::group(['prefix' => 'user'], function () {

        Route::get('/VerificationConfirm/{id}', [FrontendController::class, 'LoginByMail']);
        Route::get('/register', [FrontendController::class, 'register']);
        Route::post('/register', [FrontendController::class, 'userRegister']);
        Route::get('/otp-verify/{userid}', [FrontendController::class, 'otpView']);
        Route::post('/otp-verify', [FrontendController::class, 'otpVerify']);
        Route::get('login', [FrontendController::class, 'login'])->name('user.login');
        Route::post('/login', [FrontendController::class, 'userLogin']);
        Route::get('/resetPassword', [FrontendController::class, 'resetPassword']);
        Route::post('/resetPassword', [FrontendController::class, 'userResetPassword']);
        Route::get('/organizer-invite', [FrontendController::class, 'orgRegister'])->name('organizer.invite')->middleware('signed');
        Route::post('/organizer-invite', [FrontendController::class, 'organizerRegister'])->middleware('signed');
        Route::get('/logout', [LicenseController::class, 'adminLogout'])->name('logout-admin');
        Route::get('/logoutuser', [FrontendController::class, 'userLogout'])->name('logoutUser');
        Route::post('/search_event', [FrontendController::class, 'searchEvent']);
        Route::get('/tag/{tagname}', [FrontendController::class, 'eventsByTag']);
        Route::get('/blog-tag/{tagname}', [FrontendController::class, 'blogByTag']);
        Route::get('/FAQs', [FrontendController::class, 'Faqs']);
        Route::any('/stripe/create-session', [FrontendController::class, 'checkoutSession'])->name('stripe.checkoutSession');
        Route::get('/stripe/success', [FrontendController::class, 'stripeSuccess'])->name('stripe.success');
        Route::get('/stripe/cancel', [FrontendController::class, 'striepCancel'])->name('stripe.cancel');

        Route::any('/flutterwave/webhook', [FrontendController::class, 'flutterwaveWebhook']);
    });
    Route::group(['middleware' => 'checkStatus'], function () {

        Route::get('/all-events', [FrontendController::class, 'allEvents']);
        Route::post('/all-events', [FrontendController::class, 'allEvents']);
        Route::get('/events-category/{id}/{name}', [FrontendController::class, 'categoryEvents']);
        Route::get('/events-city/{id}/{name}', [FrontendController::class, 'cityEvents']);
        Route::get('/event-type/{type}', [FrontendController::class, 'eventType']);
        Route::get('/event/{id}/{name}', [FrontendController::class, 'eventDetail']);
        Route::get('/events/{id}', [FrontendController::class, 'eventDetail']);
        Route::get('/organizations/{id}', [FrontendController::class, 'orgDetail'])->name('organizationDetails');
        Route::post('/report-event', [FrontendController::class, 'reportEvent']);
        Route::get('/all-category', [FrontendController::class, 'allCategory']);
        Route::get('/all-blogs', [FrontendController::class, 'blogs']);
        Route::get('/blog-detail/{id}/{name}', [FrontendController::class, 'blogDetail']);
        Route::get('/contact', [FrontendController::class, 'contact']);

        Route::group(['middleware' => 'appuser'], function () {

            Route::get('email/verify/{id}/{token}', [FrontendController::class, 'emailVerify']);
            Route::get('/checkout/{id}', [FrontendController::class, 'checkout']);
            Route::post('/applyCoupon', [FrontendController::class, 'applyCoupon']);
            Route::any('/createOrder', [FrontendController::class, 'createOrder'])->name('createOrderUser');
            Route::get('/user/profile', [FrontendController::class, 'userTickets']);
            Route::get('/add-favorite/{id}/{type}', [FrontendController::class, 'addFavorite']);
            Route::get('/add-followList/{id}', [FrontendController::class, 'addFollow']);
            Route::post('/add-bio', [FrontendController::class, 'addBio']);
            Route::get('/change-password', [FrontendController::class, 'changePassword']);
            Route::post('/user-change-password', [FrontendController::class, 'changeUserPassword']);
            Route::post('/upload-profile-image', [FrontendController::class, 'uploadProfileImage']);
            Route::get('/my-tickets', [FrontendController::class, 'userTickets'])->name('myTickets');
            Route::get('/my-ticket/{id}', [FrontendController::class, 'userOrderTicket']);
            Route::post('/cancel-ticket/{id}', [FrontendController::class, 'cancelTicket'])->name('cancelTicket');
            Route::get('/messages', [MessageController::class, 'index'])->name('myMessages');
            Route::get('/messages/{id}', [MessageController::class, 'show']);
            Route::post('/messages/{id}', [MessageController::class, 'store']);
            Route::get('/message-organizer/{id}', [MessageController::class, 'startWithOrganizer']);
            Route::get('/update_profile', [FrontendController::class, 'update_profile']);
            Route::post('/update_user_profile', [FrontendController::class, 'update_user_profile']);
            Route::post('/gallery/upload', [FrontendController::class, 'uploadGalleryImage'])->name('uploadGalleryImage');
            Route::get('/gallery/{id}/delete', [FrontendController::class, 'deleteGalleryImage'])->name('deleteGalleryImage');
            Route::get('/support-tickets', [\App\Http\Controllers\SupportTicketController::class, 'index'])->name('mySupportTickets');
            Route::get('/support-tickets/create', [\App\Http\Controllers\SupportTicketController::class, 'create'])->name('createSupportTicket');
            Route::post('/support-tickets', [\App\Http\Controllers\SupportTicketController::class, 'store'])->name('storeSupportTicket');
            Route::get('/support-tickets/{id}', [\App\Http\Controllers\SupportTicketController::class, 'show'])->name('showSupportTicket');
            Route::post('/support-tickets/{id}/reply', [\App\Http\Controllers\SupportTicketController::class, 'reply'])->name('replySupportTicket');
            Route::get('/membership', [\App\Http\Controllers\MembershipController::class, 'index'])->name('myMembership');
            Route::post('/membership/upgrade/{plan}', [\App\Http\Controllers\MembershipController::class, 'upgrade'])->name('membershipUpgrade');
            Route::get('/getOrder/{id}', [FrontendController::class, 'getOrder']);
            Route::post('/add-review', [FrontendController::class, 'addReview']);
            Route::get('/web/create-payment/{id}', [FrontendController::class, 'makePayment']);
            Route::post('/web/payment/{id}', [FrontendController::class, 'initialize'])->name('frontendPay');
            Route::get('/web/rave/callback/{id}', [FrontendController::class, 'callback'])->name('frontendCallback');

            # Wallet
            Route::group(['prefix' => 'user'], function () {
                Route::get('/wallet', [WalletController::class, 'wallet'])->name('myWallet');
                Route::get('/wallet/add-money', [WalletController::class, 'addToWallet'])->name('addToWallet');
                Route::post('/deposite', [WalletController::class, 'deposite'])->name('deposite');
                Route::any('/wallet/stripe/create-session', [WalletController::class, 'checkoutSession'])->name('stripe.createSession');
                Route::get('/wallet/stripe/success', [WalletController::class, 'stripeSuccess'])->name('walletStripe.success');
                Route::get('/wallet/stripe/cancel', [WalletController::class, 'striepCancel'])->name('walletStripe.cancel');
                Route::get('/wallet/send', [WalletController::class, 'sendFundsForm'])->name('sendFunds');
                Route::post('/wallet/send', [WalletController::class, 'sendFunds'])->name('sendFundsPost');
            });
        });
    });
});

