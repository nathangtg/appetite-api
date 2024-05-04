<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderedItems;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $restaurant_id)
    {
        $restaurant_id = $request->route('restaurant_id');

        // Authenticate the user if the user is restaurant admin
        $user = Auth::user();

        if (!$user || $user->account_type !== 'restaurant') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if the user is the admin of the specified restaurant
        $restaurant = Restaurant::find($restaurant_id);

        if (!$restaurant || $restaurant->admin_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Make sure the user is the admin of that restaurant
        $orders = Order::where('restaurant_id', $restaurant_id)->get();

        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate if user is authenticated
        if (Auth::check()) {
            // Validate the request data for order creation
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'status' => 'required',
                'order_type' => 'required',
                'items' => 'required|array|min:1',
                'items.*.menu_id' => 'required|exists:menus,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Calculate total
            $total = 0;
            foreach ($request->input('items') as $item) {
                $total += $item['quantity'] * $item['price'];
            }

            // Create a new order with the calculated total
            $order = Order::create([
                'restaurant_id' => $request->route('restaurant_id'),
                'user_id' => Auth::id(),
                'email' => $request->input('email'),
                'total' => $total,
                'status' => $request->input('status'),
                'order_type' => $request->input('order_type'),
            ]);

            // If no items are sent, return an error
            if (empty($request->input('items'))) {
                return response()->json(['error' => 'At least one item is required for the order'], 400);
            }

            // Create ordered items for the order
            foreach ($request->input('items') as $item) {
                OrderedItems::create([
                    'order_id' => $order->id,
                    'menu_id' => $item['menu_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                ]);
            }

            // Return the created order
            return response()->json(['order' => $order], 201);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Request $request, Order $order)
    {
        // Get the restaurant_id and id from the route parameters
        $restaurant_id = $request->route('restaurant_id');
        $order_id = $request->route('id');

        // Find the order by id and restaurant_id
        $order = Order::where('id', $order_id)
            ->where('restaurant_id', $restaurant_id)
            ->first();

        // Validate if the user is authenticated
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if the authenticated user is the owner of the order
        if ($user->id !== $order->user_id) {
            // Check if the authenticated user is a restaurant admin
            if ($user->account_type === 'restaurant') {
                // Check if the user is the admin of the order's restaurant
                $restaurant = Restaurant::find($restaurant_id);

                if (!$restaurant || $restaurant->admin_id !== $user->id) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            }
        }

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Return the order
        return response()->json(['order' => $order], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        // Validate if the user is authenticated
        $user = Auth::user();

        $restaurant_id = $request->route('restaurant_id');
        $order_id = $request->route('id');

        // Find the order by id and restaurant_id
        $order = Order::where('id', $order_id)
            ->where('restaurant_id', $restaurant_id)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if the authenticated user is the owner of the order
        if ($user->id !== $order->user_id) {
            // Check if the authenticated user is a restaurant admin
            if ($user->account_type === 'restaurant') {
                // Check if the user is the admin of the order's restaurant
                $restaurant = Restaurant::find($restaurant_id);

                if (!$restaurant || $restaurant->admin_id !== $user->id) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            }
        }

        // Validate the request data for order update
        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|required',
            'total' => 'sometimes|required',
            'status' => 'sometimes|required',
            'order_type' => 'sometimes|required',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Update the order fields based on the provided data
        if ($request->has('email')) {
            $order->email = $request->input('email');
        }

        if ($request->has('total')) {
            $order->total = $request->input('total');
        }

        if ($request->has('status')) {
            $order->status = $request->input('status');
        }

        if ($request->has('order_type')) {
            $order->order_type = $request->input('order_type');
        }

        // Save the changes to the order
        $order->save();

        // Return a success response
        return response()->json(['message' => 'Order updated successfully', 'updated' => $order], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Order $order)
    {
        // Get the restaurant_id and id from the route parameters
        $restaurant_id = $request->route('restaurant_id');
        $order_id = $request->route('id');

        // Find the order by id and restaurant_id
        $order = Order::where('id', $order_id)
            ->where('restaurant_id', $restaurant_id)
            ->first();

        // Validate if the authenticated user is the admin of the restaurant
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if the authenticated user is the owner of the order
        if ($user->id !== $order->user_id) {
            // Check if the authenticated user is a restaurant admin
            if ($user->account_type === 'restaurant') {
                // Check if the user is the admin of the order's restaurant
                $restaurant = Restaurant::find($restaurant_id);

                if (!$restaurant || $restaurant->admin_id !== $user->id) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            }
        }

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Delete the order
        $order->delete();

        // Return a success response
        return response()->json(['message' => 'Order deleted successfully', "deleted" => $order], 200);
    }

    /**
     * Display the specified resource by user
     */

    public function userOrders(Request $request)
    {
        // Validate if user is authenticated
        if (Auth::check()) {
            // Get the user's orders
            $orders = Order::where('user_id', Auth::id())->get();

            // Check the orders length
            if (count($orders) === 0) {
                return response()->json(['error' => 'No orders found'], 404);
            }

            // Return the user's orders
            return response()->json(['orders' => $orders], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
