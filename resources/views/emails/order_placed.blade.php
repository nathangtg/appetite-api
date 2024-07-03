<!DOCTYPE html>
<html lang="en">
<head>

    {{-- Log the menu --}}
    @php
        Log::info('Order placed email sent for order ID: ' . $order->id);
        // Log the order items
        foreach ($orderItems as $item) {
            Log::info('Order item: ' . $item->menu_name . ' - Quantity: ' . $item->quantity . ' - Price: $' . $item->price);
        }
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        h3 {
            color: #555;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #555;
        }
        td {
            background-color: #fff;
        }
        p {
            margin-bottom: 5px;
        }
        .total {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Thank you for your order!</h1>
        <p>Here are the details of your order:</p>

        <h3>Order Summary</h3>
        <p><strong>Email:</strong> {{ $order->email }}</p>
        <p><strong>Status:</strong> {{ $order->status }}</p>
        <p><strong>Order Type:</strong> {{ $order->order_type }}</p>
        <p><strong>Payment Method:</strong> {{ $order->payment_method }}</p>
        <p><strong>Payment Status:</strong> {{ $order->payment_status }}</p>
        <p class="total"><strong>Total:</strong> ${{ $order->total }}</p>

        <h3>Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Menu Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orderItems as $item)
                <tr>
                    <td>{{ $item->menu_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ $item->price }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
