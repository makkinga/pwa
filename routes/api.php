<?php

use App\Notifications\TestNotification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('notification-subscription/{user}', function (Request $request, User $user) {
    $user->updatePushSubscription(
        $request->input('endpoint'),
        $request->input('keys.p256dh'),
        $request->input('keys.auth')
    );

    $user->notify(new TestNotification('Bedankt!', 'Je ontvant vanaf nu alle vd Beeten push notificaties.'));

    return response()->json(['success' => true]);
});

Route::post('/send-notification/{user}', function(Request $request, User $user){
    $user->notify(new TestNotification($request->title, $request->body));

    return response()->json([
        'success' => true
    ]);
});
