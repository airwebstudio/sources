<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\StorageFileController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\ChatsController;
use App\Http\Controllers\MeetingAvailabilityController;
use App\Http\Controllers\MeetingAvailabilityParticipantController;
use App\Http\Controllers\UserFeedItemController;
use App\Http\Controllers\MeetingAttachmentController;
use App\Http\Controllers\MeetingReportController;
use App\Http\Controllers\SellerRateController;
use App\Http\Controllers\SellerReviewController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\EventJournalController;

use App\Http\Middleware\Authenticate;
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
Route::group([
    'prefix' => 'stripe',
], function () {
    Route::get('connect', [StripeController::class, 'connect']);
    Route::get('get', [StripeController::class, 'get']);
    Route::get('link', [StripeController::class, 'link']);
    Route::get('retrieve', [StripeController::class, 'retrieve']);
    Route::any('charge', [StripeController::class, 'charge']);
    Route::any('transaction', [StripeController::class, 'transaction']);
    Route::any('balance', [StripeController::class, 'balance']);
    Route::any('payment_status', [StripeController::class, 'payment_status']);
    Route::any('history', [StripeController::class, 'history']);
    Route::any('payout', [StripeController::class, 'payout']);
    Route::any('payment_status', [StripeController::class, 'payment_status']);
    Route::any('finish_queue', [StripeController::class, 'finish_queue']);

});

Route::put('/transaction', [TransactionsController::class, 'create']);
Route::get('/transaction/{internal_transaction_id}', [TransactionsController::class, 'info']);
Route::get('/transactions', [TransactionsController::class, 'list']);

Route::get('/transactions', [TransactionsController::class, 'list']);

Route::get('/paypal/update/{id}', [App\Http\Controllers\PayPalController::class, 'update']);

Route::group([
    'prefix' => 'chats'
], function () {
    Route::get('', [ChatsController::class, 'list']);
    Route::get('room/{room_id}', [ChatsController::class, 'room']);

    Route::group([
        'prefix' => 'private'
    ], function () {
        Route::get('', [ChatsController::class, 'get_private_chats']);
    });
});

Route::group([
    'prefix' => 'user'
], function () {
    Route::post('login',        [UserController::class, 'login']);
    Route::get('logout',        [UserController::class, 'logout']);
    Route::post('refresh',      [UserController::class, 'refresh']);
    Route::get('me',            [UserController::class, 'me']);

    Route::post('login/google', [UserController::class, 'login_with_google']);
    Route::post('login/facebook',[UserController::class, 'login_with_facebook']);
    Route::post('login/apple',  [UserController::class, 'login_with_apple']);

    Route::post('forgot-password',[UserController::class, 'forgot_password']);
    Route::post('update-password',[UserController::class, 'update_password']);

    Route::put('',              [UserController::class, 'create']);
    Route::post('',             [UserController::class, 'edit']);

    Route::group([
        'prefix' => 'seller'
    ], function () {
        Route::put('',          [SellerController::class, 'create']);
        Route::get('',          [SellerController::class, 'read']);
        Route::get('info/{id}', [SellerController::class, 'info']);
        Route::post('',         [SellerController::class, 'update']);
        Route::delete('',       [SellerController::class, 'delete']);
        Route::get('find',      [SellerController::class, 'find']);
        Route::put('rate',      [SellerController::class, 'rate']);
        Route::group([
            'prefix' => 'rate'
        ], function () {
            Route::put('{seller_user_id}', [SellerRateController::class, 'create']);
            Route::get('{seller_user_id}', [SellerRateController::class, 'list']);
        });
        Route::group([
            'prefix' => 'review'
        ], function () {
            Route::put('{seller_user_id}', [SellerReviewController::class, 'create']);
            Route::get('{seller_user_id}', [SellerReviewController::class, 'list']);
        });
    });
    Route::group([
        'prefix' => 'feed-item'
    ], function () {
        Route::put('',          [UserFeedItemController::class, 'create']);
        Route::get('{id}',      [UserFeedItemController::class, 'read']);
        Route::post('{id}',     [UserFeedItemController::class, 'update']);
        Route::delete('{id}',   [UserFeedItemController::class, 'delete']);
    });
    Route::get('feed-items',    [UserFeedItemController::class, 'feed_items_for_current_user']);
});

Route::group([
    'prefix' => 'public'
], function () {
    Route::get('feed-items', [UserFeedItemController::class, 'feed_items_public']);
});

Route::group([
    'prefix' => 'meeting'
], function () {
    Route::any('',                      [MeetingController::class, 'create']);
    Route::get('upcoming_events',       [MeetingController::class, 'upcoming_events']);


    Route::any('join', [MeetingController::class, 'join_meeting']);
    Route::any('finish', [MeetingController::class, 'finish_meeting']);
    Route::any('start', [MeetingController::class, 'start_meeting']);
    Route::any('pay_minute', [MeetingController::class, 'pay_minute']);


    Route::get('{hash}',                [MeetingController::class, 'info']);
    Route::post('{hash}',               [MeetingController::class, 'update']);
    Route::delete('{hash}',             [MeetingController::class, 'decline']);

    Route::post('my/own',               [MeetingController::class, 'my_own']);
    Route::post('my/participantof',     [MeetingController::class, 'participantof']);
    Route::post('my/requests',          [MeetingController::class, 'my_requests']);
    Route::get('calendar/{user_id}',    [MeetingController::class, 'calendar']);

    Route::put('{hash}/{user_id}',      [MeetingController::class, 'add_user_to_meeting']);
    Route::post('{hash}/pay',           [MeetingController::class, 'pay']);
    Route::post('{hash}/{user_id}',     [MeetingController::class, 'approve_add_user_to_meeting']);
    Route::delete('{hash}/{user_id}',   [MeetingController::class, 'remove_user_from_meeting']);

    Route::any('sync/{hash}',           [MeetingController::class, 'data_synchronization']);

});

Route::group([
    'prefix' => 'proposal'
], function () {
    Route::post('my/own',               [ProposalController::class, 'my_own']);
    Route::post('my/requests',          [ProposalController::class, 'my_requests']);

    Route::put('',                      [ProposalController::class, 'create']);
    Route::post('{id}',                 [ProposalController::class, 'approve']);
    Route::delete('{id}',               [ProposalController::class, 'decline']);

    Route::put('{id}/{user_id}',        [ProposalController::class, 'add_user_to_propose_meeting']);
    Route::delete('{id}/{user_id}',     [ProposalController::class, 'remove_user_from_propose_meeting']);
});

Route::get('page/{slug}', [PageController::class, 'read']);
Route::get('page', [PageController::class, 'read_all']);

Route::group([
    'prefix' => 'storage'
], function () {
    Route::post('upload', [StorageFileController::class, 'upload']);
    Route::get('{unique_key}', [StorageFileController::class, 'read']);
    Route::delete('{unique_key}', [StorageFileController::class, 'delete']);
});
Route::group([
    'prefix' => 'avatar'
], function () {
    Route::post('upload', [AvatarController::class, 'upload']);
    Route::get('', [AvatarController::class, 'read']);
    Route::get('{user_id}', [AvatarController::class, 'read']);
    Route::delete('', [AvatarController::class, 'delete']);
});

/**
 * Seller
 */
Route::group([
    'prefix' => 'seller'
], function () {
    Route::get('{user_id}/availability', [MeetingAvailabilityController::class, 'get_seller_availability']);

    Route::group([
        'prefix' => 'availability'
    ], function () {
        Route::put('',                      [MeetingAvailabilityController::class, 'create']);
        Route::get('',                      [MeetingAvailabilityController::class, 'list']);
        Route::get('{id}',                  [MeetingAvailabilityController::class, 'read']);
        Route::post('{id}',                 [MeetingAvailabilityController::class, 'update']);
        Route::delete('{id}',               [MeetingAvailabilityController::class, 'decline']);

        Route::get('{id}/participants',     [MeetingAvailabilityParticipantController::class, 'get_users_list']);

        Route::group([
            'prefix' => 'participant'
        ], function () {
            Route::post('{id}',             [MeetingAvailabilityParticipantController::class, 'approve']);
            Route::delete('{id}',           [MeetingAvailabilityParticipantController::class, 'delete']);
        });
    });
});

/**
 * Buyer
 */
Route::group([
    'prefix' => 'buyer'
], function () {
    Route::put('availability/{availability_id}', [MeetingAvailabilityParticipantController::class, 'add_user_to_availability_meeting']);
});

Route::group([
    'prefix' => 'attachments'
], function () {
    Route::post('{meeting_hash}', [MeetingAttachmentController::class, 'upload']);
    Route::get('{meeting_hash}', [MeetingAttachmentController::class, 'list']);
    Route::get('{meeting_hash}/{unique_key}', [MeetingAttachmentController::class, 'read']);
    Route::delete('{meeting_hash}/{unique_key}', [MeetingAttachmentController::class, 'delete']);
});
Route::group([
    'prefix' => 'events'
], function () {
    Route::get('me', [EventJournalController::class, 'me']);
});
Route::group([
    'prefix' => 'report'
], function () {
    Route::post('meeting/{hash}', [MeetingReportController::class, 'send_report']);
});
