<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('movies', MovieController::class);
});

Route::middleware('auth:sanctum')->get('/genres', function () {
    return Movie::getUniqueGenres();
});

Route::get('/movies-test', function () {
    // Get all movies from the database directly
    $allMovies = \Illuminate\Support\Facades\DB::table('movies')->get();

    // Get the current authenticated user
    $user = auth()->user();

    // Get movies for the authenticated user
    $userMovies = \Illuminate\Support\Facades\DB::table('movies')
                  ->where('user_id', $user ? $user->id : 0)
                  ->get();

    // Try to insert a test movie
    $testId = null;
    try {
        $testId = \Illuminate\Support\Facades\DB::table('movies')->insertGetId([
            'title' => 'TEST MOVIE ' . rand(1000, 9999),
            'genre' => 'Test',
            'release_year' => 2025,
            'description' => 'This is a test movie to verify database write access.',
            'user_id' => $user ? $user->id : 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Database test failed',
            'error' => $e->getMessage(),
            'all_movies' => $allMovies,
            'user_movies' => $userMovies,
            'user' => $user
        ], 500);
    }

    // Fetch all movies again after inserting
    $updatedMovies = \Illuminate\Support\Facades\DB::table('movies')->get();

    return response()->json([
        'message' => 'Database test successful',
        'user' => $user,
        'all_movies_count' => count($allMovies),
        'all_movies' => $allMovies,
        'user_movies_count' => count($userMovies),
        'user_movies' => $userMovies,
        'test_movie_id' => $testId,
        'updated_movies_count' => count($updatedMovies)
    ]);
});

// Also add a public test route that doesn't require authentication
Route::get('/public-test', function () {
    return response()->json([
        'message' => 'API is working',
        'time' => now()->toDateTimeString()
    ]);
});
