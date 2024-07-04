<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlacedMail;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderedItems;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        // ALso get ordered item

        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     // Validate if user is authenticated
    //     if (Auth::check()) {
    //         // Validate the request data for order creation
    //         $validator = Validator::make($request->all(), [
    //             'email' => 'required',
    //             'status' => 'required',
    //             'order_type' => 'required',
    //             'payment_method' => 'required',
    //             'payment_status' => 'required',
    //         'items' => 'required|array|min:1',
    //             'items.*.menu_id' => 'required|exists:menus,id',
    //             'items.*.quantity' => 'required|integer|min:1',
    //             'items.*.price' => 'required|numeric|min:0',
    //         ]);

    //         // Check if validation fails
    //         if ($validator->fails()) {
    //             return response()->json(['error' => $validator->errors()], 400);
    //         }

    //         // Calculate total
    //         $total = 0;
    //         foreach ($request->input('items') as $item) {
    //             $total += $item['quantity'] * $item['price'];
    //         }

    //         // Create a new order with the calculated total
    //         $order = Order::create([
    //             'restaurant_id' => $request->route('restaurant_id'),
    //             'user_id' => Auth::id(),
    //             'email' => $request->input('email'),
    //             'total' => $total,
    //             'status' => $request->input('status'),
    //             'order_type' => $request->input('order_type'),
    //             'payment_method' => $request->input('payment_method'),
    //             'payment_status' => $request->input('payment_status'),
    //         ]);

    //         // If no items are sent, return an error
    //         if (empty($request->input('items'))) {
    //             return response()->json(['error' => 'At least one item is required for the order'], 400);
    //         }

    //         // Create ordered items for the order
    //         $orderItems = [];
    //         foreach ($request->input('items') as $item) {
    //             $orderItem = OrderedItems::create([
    //                 'order_id' => $order->id,
    //                 'menu_id' => $item['menu_id'],
    //                 'quantity' => $item['quantity'],
    //                 'price' => $item['price'],
    //                 'total' => $item['quantity'] * $item['price'],
    //                 'note' => $item['note'] ?? '',
    //             ]);
    //             $orderItems[] = $orderItem;
    //         }

    //         Mail::to($request->input('email'))->send(new OrderPlacedMail($order, $orderItems));

    //         // Return the created order
    //         return response()->json(['order' => $order], 201);
    //     } else {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }
    // }

    public function store(Request $request)
    {
        // Validate if user is authenticated
        if (Auth::check()) {
            // Validate the request data for order creation
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'status' => 'required',
                'order_type' => 'required',
                'payment_method' => 'required',
                'payment_status' => 'required',
                'table_number' => [
                    'required_if:order_type,dine-in',
                    'integer',
                    'min:1',
                    function ($attribute, $value, $fail) use ($request) {
                        $restaurant = Restaurant::find($request->route('restaurant_id'));
                        if ($restaurant && $value > $restaurant->number_of_tables) {
                            $fail('The selected table number is invalid.');
                        }
                    },
                ],
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
                'table_number' => $request->input('table_number', null),
            ]);

            // If no items are sent, return an error
            if (empty($request->input('items'))) {
                return response()->json(['error' => 'At least one item is required for the order'], 400);
            }

            // Create ordered items for the order with menu names
            $orderItems = [];
            foreach ($request->input('items') as $item) {

                // Fetch menu name using join
                $menu = Menu::where('id', $item['menu_id'])->first();

                // Log error if menu not found
                if (!$menu) {
                    return response()->json(['error' => 'Menu not found'], 404);
                }

                $orderItem = OrderedItems::create([
                    'order_id' => $order->id,
                    'menu_id' => $item['menu_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                    'note' => $item['note'] ?? '',
                ]);

                // Add menu name to order item
                $orderItem->menu_name = $menu->name;

                $orderItems[] = $orderItem;
            }

            Log::info($orderItems);

            // Send email with order details
            Mail::to($request->input('email'))->send(new OrderPlacedMail($order, $orderItems));

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

        // Check if the authenticated user is the owner of the order or the admin of the restaurant
        if ($user->id !== $order->user_id) {
            // Check if the authenticated user is a restaurant admin or owner
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
            'payment_method' => 'sometimes|required',
            'payment_status' => 'sometimes|required',
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

        if ($request->has('payment_method')) {
            $order->payment_method = $request->input('payment_method');
        }

        if ($request->has('payment_status')) {
            $order->payment_status = $request->input('payment_status');
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
             if ($orders->isEmpty()) {
                 return response()->json(['error' => 'No orders found'], 404);
             }

             // Check if each order has been rated
             $ordersWithRating = $orders->map(function ($order) {
                 $order->is_rated = $order->ratings()->exists();
                 return $order;
             });

             // Return the user's orders with is_rated flag
             return response()->json(['orders' => $ordersWithRating], 200);
         } else {
             return response()->json(['error' => 'Unauthorized'], 401);
         }
     }


    // ! Get order and ordered items
    public function getOrderAndItems(Request $request, Order $order)
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

        // Get the ordered items for the order
        $items = OrderedItems::where('order_id', $order->id)->get();

        // Get the menu name for each ordered item
        foreach ($items as $item) {
            $item->menu_item = $item->menu->name;
        }

        // Return the order and ordered items
        return response()->json(['order' => $order, 'items' => $items], 200);
    }
}
