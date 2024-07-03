<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RatingsController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|numeric|exists:restaurants,id',
            'order_id' => 'required|numeric|exists:orders,id',
            'rating' => 'required|numeric|min:0|max:5',
            'comment' => 'sometimes|string|max:255',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Create a new rating
        $rating = Rating::create([
            'user_id' => Auth::id(),
            'restaurant_id' => $request->route('restaurant_id'),
            'order_id' => $request->route('order_id'),
            'rating' => $request->rating,
            'comment' => $request->input('comment', null), // Use input() method for optional fields
        ]);

        // Return a success response
        return response()->json([
            'message' => 'Rating created successfully',
            'rating' => $rating,
        ], 201);
    }

    /**
     * Update an existing rating for a restaurant
     */
    public function update(Request $request, $id)
    {
        // Find the rating by ID
        $rating = Rating::findOrFail($id);

        // Check if the authenticated user is authorized to update the rating
        if ($rating->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:0|max:5',
            'comment' => 'sometimes|string|max:255',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Update the rating with the new data
        $rating->update([
            'rating' => $request->rating,
            'comment' => $request->input('comment', $rating->comment), // Keep existing value if not provided
        ]);

        // Return a success response
        return response()->json([
            'message' => 'Rating updated successfully',
            'rating' => $rating,
        ], 200);
    }
}
