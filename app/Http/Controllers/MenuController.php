<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($restaurant_id, Request $request)
    {
        // Get restaurant id from the route parameters
        $restaurant_id = $request->route('restaurant_id');

        // Retrieve all menus for the specified restaurant
        $menus = Menu::where('restaurant_id', $restaurant_id)->get();

        // Transform the menu items to include the full URL of the image
        $menus->transform(function ($menu) {
            $menu->menuImage = url($menu->image);
            return $menu;
        });

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
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            // Prepare data for creating the menu
            $data = $request->only(['name', 'category', 'description', 'price']);
            $data['restaurant_id'] = $restaurant_id;

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('public/menus');
                $data['image'] = url(Storage::url($imagePath));
            }

            // Create the menu
            $menu = Menu::create($data);

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
    public function show(Request $request, Menu $menu)
    {
        // Get the restaurant_id and id from the route parameters
        $restaurantId = $request->route('restaurant_id');
        $menuId = $request->route('id');

        // Find the menu by id and restaurant_id
        $menu = Menu::where('id', $menuId)
                    ->where('restaurant_id', $restaurantId)
                    ->first();

        if (!$menu) {
            return response()->json(['error' => 'Menu not found'], 404);
        }

        // Return the menu
        return response()->json(['menu' => $menu], 200);
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
            'display' => 'sometimes|required',
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
            // Store the image in the public storage with global url
            $menu->image = url(Storage::url($request->file('image')->store('public/menus')));
        }

        if ($request->has('display')) {
            $menu->display = $request->input('display');
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

    public function upload(Request $request) {
        // Reetreive the restaurant_id and id from the route parameters
        $restaurant_id = $request->route('restaurant_id');
        $menu_id = $request->route('id');

        // Validate if the authenticated user is the admin of the restaurant
        $user = Auth::user();

        if (!$user || $user->account_type !== 'restaurant') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $restaurant = Restaurant::find($restaurant_id);

        if (!$restaurant || $restaurant->admin_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Find the menu by id and restaurant_id
        $menu = Menu::where('id', $menu_id)
                    ->where('restaurant_id', $restaurant_id)
                    ->first();

        if (!$menu) {
            return response()->json(['error' => 'Menu not found'], 404);
        }

        $rules = [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules);

        // Handle image upload
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Upload the new image
        $imagePath = $request->file('image')->store('public/menus');
        $imagePath = url(Storage::url($imagePath));

        // Update the menu with the new image path
        $menu->image = $imagePath;

        // Save the changes to the menu
        $menu->save();

        // Return a success response
        return response()->json(['message' => 'Menu image uploaded successfully', 'menu' => $menu], 200);
    }

    public function clientIndex($restaurant_id, Request $request)
    {
        // Get restaurant id from the route parameters
        $restaurant_id = $request->route('restaurant_id');

        // Retrieve all menus for the specified restaurant and filter by displayed menus
        $menus = Menu::where('restaurant_id', $restaurant_id)
                     ->where('display', 'yes')
                     ->get();

        // Transform the menu items to include the full URL of the image
        $menus->transform(function ($menu) {
            $menu->menuImage = url($menu->image);
            return $menu;
        });

        // Return the menus as JSON response
        return response()->json(['menus' => $menus], 200);
    }
}
