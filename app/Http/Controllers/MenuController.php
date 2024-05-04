<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($restaurant_id)
    {
        // Check if the authenticated user is the admin of the specified restaurant
        $user = Auth::user();
        if (!$user || $user->account_type !== 'restaurant') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $restaurant = Restaurant::find($restaurant_id);
        if (!$restaurant || $restaurant->admin_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Retrieve all menus for the specified restaurant
        $menus = Menu::where('restaurant_id', $restaurant_id)->get();

        // Return the menus as JSON response
        return response()->json(['menus' => $menus], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $restaurant_id)
    {
        // Validate if user is authenticated and a restaurant
        if (Auth::check() && Auth::user()->account_type === 'restaurant') {
            // Check if the authenticated user is the admin of the specified restaurant
            $user_id = Auth::id();
            $isAdmin = Restaurant::where('id', $restaurant_id)->where('admin_id', $user_id)->exists();

            if (!$isAdmin) {
                return response()->json(['error' => 'Only restaurant admins can create menus for their restaurants'], 403);
            }

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'description' => 'required',
                'price' => 'required',
                'image' => 'required',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            // Create the menu
            $menu = Menu::create([
                'restaurant_id' => $restaurant_id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'image' => $request->image,
            ]);

            // Return a success response
            return response()->json(['menu' => $menu], 201);
        } else {
            // If the user is not authenticated or not a restaurant, return an error response
            return response()->json(['error' => 'Only authenticated restaurant admins can create menus'], 403);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Menu $menu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        // Get the restaurant_id and id from the route parameters
        $restaurantId = $request->route('restaurant_id');
        $menuId = $request->route('id');

        // Validate if the authenticated user is the admin of the restaurant
        $user = Auth::user();

        if (!$user || $user->account_type !== 'restaurant') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $restaurant = Restaurant::find($restaurantId);

        if (!$restaurant || $restaurant->admin_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Find the menu by id and restaurant_id
        $menu = Menu::where('id', $menuId)
                    ->where('restaurant_id', $restaurantId)
                    ->first();

        if (!$menu) {
            return response()->json(['error' => 'Menu not found'], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required',
            'description' => 'sometimes|required',
            'price' => 'sometimes|required',
            'image' => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Update the menu fields based on the provided data
        if ($request->has('name')) {
            $menu->name = $request->input('name');
        }

        if ($request->has('description')) {
            $menu->description = $request->input('description');
        }

        if ($request->has('price')) {
            $menu->price = $request->input('price');
        }

        if ($request->has('image')) {
            $menu->image = $request->input('image');
        }

        // Save the changes to the menu
        $menu->save();

        // Return a success response
        return response()->json(['message' => 'Menu updated successfully', "updated" => $menu], 200);

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Get the restaurant_id and id from the route parameters
        $restaurantId = $request->route('restaurant_id');
        $menuId = $request->route('id');

        // Validate if the authenticated user is the admin of the restaurant
        $user = Auth::user();

        if (!$user || $user->account_type !== 'restaurant') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $restaurant = Restaurant::find($restaurantId);

        if (!$restaurant || $restaurant->admin_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Find the menu by id and restaurant_id
        $menu = Menu::where('id', $menuId)
                    ->where('restaurant_id', $restaurantId)
                    ->first();

        if (!$menu) {
            return response()->json(['error' => 'Menu not found'], 404);
        }

        // Delete the menu
        $menu->delete();

        // Return a success response
        return response()->json(['message' => 'Menu deleted successfully'], 200);
    }
}
