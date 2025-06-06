<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Navbar</title>
    <link rel="stylesheet" href="{{ mix('css/navbar.css') }}">
</head>
<body>
    <nav>
        <ul>
            <li><a href="{{ route('video-requests.create') }}">Create an order</a></li>
            <li><a href="{{ route('order.index') }}">View Orders</a></li>
            <li><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        </ul>
    </nav>
</body>
</html>