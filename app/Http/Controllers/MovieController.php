<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
   public function index(Request $request)
   {
        // Enhanced logging for debugging
        Log::info('Movies index called by user: ' . $request->user()->id);
        Log::info('Filter parameters: ' . json_encode($request->all()));

        // Start with a query that only includes the current user's movies
        $query = Movie::query()->where('user_id', $request->user()->id);
        Log::info('Filtering movies for user_id: ' . $request->user()->id);

        // Search by title - improved to be case-insensitive and search within the title
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            Log::info('Searching for: ' . $searchTerm);
            $query->where('title', 'like', '%' . $searchTerm . '%');
        }

        // Filter by genre - exact match for better accuracy
        if ($request->has('genre') && !empty($request->genre)) {
            Log::info('Filtering by genre: ' . $request->genre);
            $query->where('genre', $request->genre);
        }

        // Filter by release year - exact match
        if ($request->has('year') && !empty($request->year)) {
            Log::info('Filtering by year: ' . $request->year);
            $query->where('release_year', $request->year);
        }

        // Get movies that belong to the current user and match the filters
        $movies = $query->get();

        // Debug log the SQL query and results
        Log::info('SQL Query: ' . $query->toSql());
        Log::info('Query bindings: ' . json_encode($query->getBindings()));
        Log::info('Found ' . count($movies) . ' movies for user ' . $request->user()->id);

        return response()->json($movies);
   }

   public function store(Request $request)
   {
        Log::info('Store movie called by user ID: ' . $request->user()->id);
        Log::info('Movie data: ' . json_encode($request->all()));

        $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'required|string|max:100',
            'release_year' => 'required|integer|min:1900|max:' . (date('Y') + 5),
            'description' => 'nullable|string',
        ]);

        // Create the movie with explicit user_id assignment
        $movie = new Movie();
        $movie->title = $request->title;
        $movie->genre = $request->genre;
        $movie->release_year = $request->release_year;
        $movie->description = $request->description;
        $movie->user_id = $request->user()->id;
        $movie->save();

        // Verify the movie was created correctly
        Log::info('Movie created: ' . json_encode($movie->toArray()));

        return response()->json($movie, 201);
   }

   public function show(Movie $movie, Request $request)
   {
        // Debug logging
        Log::info('Show movie called for movie ID: ' . $movie->id);
        Log::info('Authenticated user ID: ' . $request->user()->id);
        Log::info('Movie user ID: ' . $movie->user_id);

        // Check if the movie belongs to the authenticated user
        if ($movie->user_id !== $request->user()->id) {
            Log::warning('Unauthorized access attempt to movie ID: ' . $movie->id . ' by user ID: ' . $request->user()->id);
            return response()->json(['message' => 'Unauthorized. This movie does not belong to you.'], 403);
        }

        return $movie;
   }

   public function update(Request $request, Movie $movie)
   {
        // Debug logging
        Log::info('Update movie called for movie ID: ' . $movie->id);
        Log::info('Authenticated user ID: ' . $request->user()->id);
        Log::info('Movie user ID: ' . $movie->user_id);

        // Check if the movie belongs to the authenticated user
        if ($movie->user_id !== $request->user()->id) {
            Log::warning('Unauthorized update attempt to movie ID: ' . $movie->id . ' by user ID: ' . $request->user()->id);
            return response()->json(['message' => 'Unauthorized. You cannot edit a movie that does not belong to you.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'required|string|max:100',
            'release_year' => 'required|integer|min:1900|max:' . (date('Y') + 5),
            'description' => 'nullable|string',
        ]);

        $movie->update($request->all());
        Log::info('Movie updated ID: ' . $movie->id . ' by user ID: ' . $request->user()->id);

        return response()->json($movie);
   }

   public function destroy(Movie $movie, Request $request)
   {
        // Debug logging
        Log::info('Delete movie called for movie ID: ' . $movie->id);
        Log::info('Authenticated user ID: ' . $request->user()->id);
        Log::info('Movie user ID: ' . $movie->user_id);

        // Check if the movie belongs to the authenticated user
        if ($movie->user_id !== $request->user()->id) {
            Log::warning('Unauthorized delete attempt to movie ID: ' . $movie->id . ' by user ID: ' . $request->user()->id);
            return response()->json(['message' => 'Unauthorized. You cannot delete a movie that does not belong to you.'], 403);
        }

        $movie->delete();
        Log::info('Movie deleted ID: ' . $movie->id . ' by user ID: ' . $request->user()->id);

        return response()->json(null, 204);
   }
}
