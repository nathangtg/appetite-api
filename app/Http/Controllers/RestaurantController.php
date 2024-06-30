<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return response()->json(Restaurant::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'address' => 'required',
            'preparation_time' => 'required|numeric',
            'cuisine' => 'required',
            'price_range' => 'required',
            'image_path' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Get the authenticated user
        $user = $request->user();

        // Set the image path to null by default
        $imagePath = null;

        // Check Image Upload
        if ($request->hasFile('image_path')) {
            $imagePath = $request->file('image_path')->store('public/restaurants');
            $imagePath = url(Storage::url($imagePath));
        }

        // Check if the user is authorized to create a restaurant
        if ($user && $user->account_type === 'restaurant') {
            // Create the restaurant with admin_id set to the authenticated user's ID
            $restaurant = Restaurant::create([
                'admin_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'address' => $request->address,
                'preparation_time' => $request->preparation_time,
                'cuisine' => $request->cuisine,
                'price_range' => $request->price_range,
                'image_path' => $imagePath,
            ]);

            // Return a success response
            return response()->json([
                'message' => 'Restaurant created successfully',
                'restaurant' => $restaurant,
            ], 201);
        }

        // If the user is not authorized, return an error response
        return response()->json(['error' => 'You are not authorized to create a restaurant'], 403);
    }

    public function recentOrderedRestaurants(Request $request, User $user) {
        // Authenticate the user
        $user = $request->user();

        // Fetch the user ID
        $userId = $user->id;

        // Fetch the orders based on the user ID
        $orders = Order::where('user_id', $userId)->get();

        // Get the restaurant IDs from the orders
        $restaurantIds = $orders->pluck('restaurant_id')->unique();

        // Fetch the restaurants based on the restaurant IDs
        $restaurants = Restaurant::whereIn('id', $restaurantIds)->get();

        // Return the restaurants
        return response()->json($restaurants, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        // Find the restaurant by ID
        $restaurant = Restaurant::find($id);

        // Check if the restaurant exists
        if ($restaurant) {
            // Return the requested restaurant
            return response()->json($restaurant, 200);
        } else {
            // If the requested restaurant does not exist, return a not found response
            return response()->json(['error' => 'Restaurant not found'], 404);
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // Retrieve the restaurant_id from the route parameters
        $id = $request->route('id');

        // Retrieve the restaurant based on the provided restaurant_id ($id)
        $restaurant = Restaurant::findOrFail($id);

        // Check if the authenticated user is the admin of the restaurant
        if ($restaurant->admin_id === auth()->user()->id) {
            // Define validation rules for the request data
            $rules = [
                'name' => 'sometimes|required',
                'description' => 'sometimes|required',
                'address' => 'sometimes|required',
                'preparation_time' => 'sometimes|required|numeric',
                'cuisine' => 'sometimes|required',
                'price_range' => 'sometimes|required',
                'image_path' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_open' => 'sometimes|boolean',
                'rating' => 'sometimes|numeric',
            ];

            // Validate the request data based on the defined rules
            $validator = Validator::make($request->all(), $rules);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            // Update only the provided fields
            $data = $request->only([
                'name', 'description', 'address', 'preparation_time', 'cuisine', 'price_range', 'image_path', 'is_open', 'rating'
            ]);

            // Handle image upload
            // ! This does not work I don't know why please help me

            if ($request->hasFile('image_path')) {
                Log::info('Image Path Exists');
                $filesystem = new Filesystem();
                $filesystem->makeDirectory(Storage::path('public/restaurants'), 0755, true);
                $imagePath = $request->file('image_path')->store('public');
                $data['image_path'] = $imagePath;
                $restaurant->image_path = $imagePath;
                $restaurant->save();
            }

            Log::info($data);

            // Update the restaurant with the new data
            $restaurant->save($data);

            // Change the image path if a new image was uploaded
            if (isset($imagePath)) {
                $restaurant->image_path = $imagePath;
                $restaurant->save();
            }

            Restaurant::where('id', $id)->update($data);

            // Return a success response
            return response()->json([
                'message' => 'Restaurant updated successfully',
                'restaurant' => $restaurant->refresh(), // Refresh the restaurant instance to get the updated values
            ], 200);
        }

        // If the user is not authorized, return an error response
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Retrieve the restaurant_id from the route parameters
        $id = $request->route('id');

        // Retrieve the restaurant based on the provided restaurant_id ($id)
        $restaurant = Restaurant::findOrFail($id);

        // Check if the authenticated user is the admin of the restaurant
        if ($restaurant->admin_id === auth()->user()->id) {
            // Delete the restaurant
            $restaurant->delete();

            // Return a success response
            return response()->json(['message' => 'Restaurant deleted successfully'], 200);
        }

        // If the user is not authorized, return an error response
        return response()->json(['error' => 'Unauthorized'], 403);
    }


    public function upload(Request $request) {
        // Retrieve the restaurant_id from the route parameters
        $id = $request->route('id');

        // Retrieve the restaurant based on the provided restaurant_id ($id)
        $restaurant = Restaurant::findOrFail($id);

        // Check if the authenticated user is the admin of the restaurant
        if ($restaurant->admin_id === auth()->user()->id) {
            // Define validation rules for the request data
            $rules = [
                'image_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ];

            // Validate the request data based on the defined rules
            $validator = Validator::make($request->all(), $rules);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            // Handle image upload
            $imagePath = $request->file('image_path')->store('public/restaurants');
            $imagePath = url(Storage::url($imagePath));

            // Update the restaurant with the new image path
            $restaurant->image_path = $imagePath;
            $restaurant->save();

            // Return a success response
            return response()->json([
                'message' => 'Restaurant picture updated successfully',
                'restaurant' => $restaurant->refresh(), // Refresh the restaurant instance to get the updated values
            ], 200);
        }

        // If the user is not authorized, return an error response
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // ! Admin Fetch
    public function adminIndex(Request $request) {
        // Get the authenticated user
        $user = $request->user();

        // Check if the user is an admin
        if ($user && $user->account_type === 'restaurant') {
            // Return all restaurants that belong to the admin
            return response()->json(Restaurant::where('admin_id', $user->id)->get(), 200);
        }

        // If the user is not authorized, return an error response
        return response()->json(['error' => 'Unauthorized'], 403);
    }


    public function adminIndexID(Request $request, $restaurant_id) {
        // Get the authenticated user
        $user = $request->user();
        $restaurant_id = $request->route('restaurant_id');

        // Check if the user is an admin
        if ($user && $user->account_type === 'restaurant') {
            // Return the restaurant that belongs to the admin
            return response()->json(Restaurant::where('admin_id', $user->id)->where('id', $restaurant_id)->get(), 200);
        }

        // If the user is not authorized, return an error response
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function giveRating(Request $request) {

        if(Auth::user()->account_type !== 'customer') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Retrieve the restaurant_id from the route parameters
        $id = $request->route('id');

        // Retrieve the restaurant based on the provided restaurant_id ($id)
        $restaurant = Restaurant::findOrFail($id);

        // Define validation rules for the request data
        $rules = [
            'rating' => 'required|numeric|min:0|max:5',
        ];

        // Validate the request data based on the defined rules
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Update the restaurant with the new rating
        $restaurant->rating = $request->rating;
        $restaurant->save();

        // Return a success response
        return response()->json([
            'message' => 'Rating updated successfully',
            'restaurant' => $restaurant->refresh(), // Refresh the restaurant instance to get the updated values
        ], 200);
    }
}
