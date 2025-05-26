<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Video Requests</title>
    <link href="{{ mix('css/admin/viewOrders.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <h1>Orders</h1>
        @if (session('success'))
            <p style="color: green">{{ session('success') }}</p>
        @elseif (session('error'))
            <p style="color: red">{{ session('error') }}</p>
        @endif
        <div class="header">
            {{ $orders->links() }}
        </div>
        <div class="orders">
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
                <tbody>
                    @foreach ($orders as $index => $order)
                    <tr class="orderRow">
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
                        <td>₹{{ $order->animation_required ? ($order->num_modules * 3) * 3000 : ($order->num_modules * 3) * 2400 }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('video-requests.create', [...$order->toArray()]) }}" class="btn btn-secondary">Edit</a>
                                <form method="POST" action={{ route("admin.orders.delete", ['id' => $order->id])}}>
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-outline-danger">Delete</button>
                                </form>
                                <a href="{{ route('admin.orders.view', ['id' => $order->id]) }}" class="btn {{$order->video_path ? 'btn-success' : 'btn-primary'}}">View Order</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <hr>
    </div>
    <div class="footer">
        <p>&copy; {{ date('Y') }} ByteEDGE &bull; All rights reserved.</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
