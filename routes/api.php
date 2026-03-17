<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Http\Request;
use App\Models\User;
Route::get('/test', function () {
    return response()->json(['message' => 'API working']);
});
Route::post('/login', function (Request $request) {

    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    return response()->json([
        'success' => true,
        'token' => $user->createToken('api-token')->plainTextToken
    ]);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/tables', [TableController::class, 'index']);   // list
    Route::post('/tables', [TableController::class, 'store']);  // create
    Route::get('/tables/{id}', [TableController::class, 'show']); // single
    Route::put('/tables/{id}', [TableController::class, 'update']); // update
    Route::delete('/tables/{id}', [TableController::class, 'destroy']); // delete

    Route::get('/availability', [ReservationController::class, 'availability']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::put('/reservations/{id}', [ReservationController::class, 'update']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'cancel']);







});



