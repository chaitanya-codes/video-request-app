<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ mix('css/admin/dashboard.css') }}">
</head>
<body>
    @include('navbar')
    <div class="container">
        <h1>Admin Dashboard</h1>
        <p>Welcome to the admin dashboard. Here you can manage orders, users, and more.</p>
        @php
            function getOrders($user, $orders, $orderStatus) {
                $orderDetails = '';
                $count = 0;
                foreach ($orders as $index => $order) {
                    if ($order['user_id'] !== $user->id) {
                        continue;
                    }
                    $status = $orderStatus[$order->id] ?? null;
                    if ($status) {
                        $stageText = $status->stage > 4 ? "Completed" : ["script", "voiceover", "segment", "final video"][$status->stage - 1];
                        $orderDetails .= "<a class=\"btn btn-secondary\" href=\"/admin/orders/{$order['id']}\">Order #{$order['id']} ($stageText)</a><br>";
                    }
                    $count++;
                }
                return "$count order(s)<br>$orderDetails";
            }
        @endphp
        <div class="recent-orders">
            <h2>Recent Orders <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">View All</a></h2>
            <table class="table table-striped table-primary table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Video Name</th>
                        <th>Description</th>
                        <th>Orientation</th>
                        <th>Output Format</th>
                        <th>Avatar Gender</th>
                        <th>Modules Count</th>
                        <th>Expected Duration</th>
                        <th>Brand Theme</th>
                        <th>Design Notes</th>
                        <th>2D Animation Required</th>
                        <th>Expected Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    @foreach ($orders as $index => $order)
                        @php
                            $matched = $orderStatus[$order->id] ?? null;
                        @endphp
                        <tr class="orderRow{{ ($matched && $matched->stage > 4) ? ' table-success' : '' }}">
                            <td>{{ ($orders->currentPage() - 1) * $orders->perPage() + $index + 1 }}</td>
                            <td>{{ $order->video_name }}</td>
                            <td>{{ $order->description }}</td>
                            <td>{{ ucfirst($order->orientation) }}</td>
                            <td>{{ strtoupper($order->output_format) }}</td>
                            <td>{{ ucfirst($order->avatar_gender) }}</td>
                            <td>{{ $order->num_modules }}</td>
                            <td>{{ $order->num_modules ? $order->num_modules * 3 : 'N/A' }}</td>
                            <td>{{ $order->brand_theme }}</td>
                            <td>{{ $order->brand_design_notes }}</td>
                            <td>{{ $order->animation_required ? 'Yes' : 'No' }}</td>
                            <td>₹{{ $order->animation_required ? $order->num_modules * 3 * 3000 : $order->num_modules * 3 * 2400 }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('video-requests.create', array_merge($order->toArray(), ['edit' => 'true', 'id' => $order->id])) }}"
                                        class="btn btn-secondary">Edit</a>
                                    <form method="POST"
                                        action={{ route('admin.orders.delete', ['id' => $order->id]) }}>
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger">Delete</button>
                                    </form>
                                    <a href="{{ route('admin.orders.view', ['id' => $order->id]) }}" class="btn btn-primary">View Order</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <hr>
        <div class="recent-users">
            <h2>Users</h2>
            {{ $users->links() }}
            <table class="table table-striped table-primary table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Orders</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at }}</td>
                            <td>{!! getOrders($user, $orders, $orderStatus) !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
