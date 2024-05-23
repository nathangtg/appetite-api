<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderedItems;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class OrderedItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all the ordered items
        $orderedItems = OrderedItems::all();

        return response()->json([
            'success' => true,
            'message' => 'List of all ordered items',
            'data' => $orderedItems
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate if user is authenticated
        if (Auth::check()) {
            // Validate the request data for order creation
            $validator = FacadesValidator::make($request->all(), [
                'email' => 'required',
                'status' => 'required',
                'order_type' => 'required',
                'payment_method' => 'required',
                'payment_status' => 'required',
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
                'payment_method' => $request->input('payment_method'),
                'payment_status' => $request->input('payment_status'),
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
    public function showByRestaurant(OrderedItems $orderedItems, Request $request)
    {
        // Fetch the ordered item
        $orderedItem = OrderedItems::find($orderedItems);

        // Get the Restaurant ID from Parameter in URL
        $restaurant_id = $request->route('restaurant_id');

        // Add the condiitons to filter the data
        $order = Order::where('restaurant_id', $restaurant_id)->get();

        // Filter the data based off the order
        $orderedItem = OrderedItems::whereIn('order_id', $order->pluck('id'))->get();

        // Check if the ordered item exists
        if ($orderedItem) {
            return response()->json([
                'success' => true,
                'message' => 'Ordered item found',
                'data' => $orderedItem
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Ordered item not found',
                'data' => null
            ], 404);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderedItems $orderedItems)
    {
        // Update the ordered item
        // Get the order ID from the URL
        $order_id = $request->route('order_id');
        $ordered_item_id = $request->route('id');

        // Find the ordered item from the restaurant and order
        $orderedItem = OrderedItems::where('order_id', $order_id)->where('id', $ordered_item_id)->first();

        // Check if the ordered item exists
        if ($orderedItem) {
            // Validate the request data for order creation
            $validator = FacadesValidator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Update the ordered item
            $orderedItem->quantity = $request->input('quantity');
            $orderedItem->price = $request->input('price');
            $orderedItem->total = $request->input('quantity') * $request->input('price');
            $orderedItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Ordered item updated',
                'data' => $orderedItem
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Ordered item not found',
                'data' => null
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderedItems $orderedItems, Request $request)
    {
        // Check if user is authenticated
        $user = Auth::user();
        $restaurant = $request->route('restaurant_id');
        $order = $request->route('order_id');

        // Get the user ID of the order
        $order_placed = Order::find($order);
        $user_order = $order_placed->user_id;

        // Find the restaurant
        $restaurant = Restaurant::find($restaurant);

        // Check if user is restaurant
        if($user && $user->account_type  === 'restaurant' || $user && $user->id === $user_order) {

            // Check if the user is the admin of the restaurant
            // Or check if the user is the one who placed the order
            if($user->id == $restaurant->admin_id || $user->id == $user_order) {

                // Get the specific ordered item to delete from the url
                // Parameter should check Restaurant ID, Order ID, and Ordered Item ID
                $order_id = $request->route('order_id');
                $ordered_item_id = $request->route('id');
                $restaurant_id = $request->route('restaurant_id');

                // Find the Order
                $order = Order::where('restaurant_id', $restaurant_id)->where('id', $order_id)->first();

                // Check if the order exists
                // If order exists, find the specific ordered item
                if ($order) {
                    $orderedItem = OrderedItems::where('order_id', $order->id)->where('id', $ordered_item_id)->first();

                    // Check if the ordered item exists
                    if ($orderedItem) {
                        // Delete the ordered item
                        $orderedItem->delete();

                        return response()->json([
                            'success' => true,
                            'message' => 'Ordered item deleted',
                            'data' => $orderedItem
                        ], 200);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Ordered item not found',
                            'data' => null
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found',
                        'data' => null
                    ], 404);
                }
            }
        }

    }
}
