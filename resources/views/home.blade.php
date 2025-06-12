<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Order Tracker - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(270deg, #21304d, #1552a1);
            color: beige;
        }
    </style>
</head>
<body>
    <div class="container py-5 text-center">
        <h1 class="mb-4 bold">Welcome to Order Tracker</h1>
        <div class="d-flex flex-column gap-3 justify-content-center align-items-center" style="max-width: 400px; margin: 0 auto; height: 50vh">
            <a href="{{ route('video-requests.create') }}" class="btn btn-success btn-lg w-100">
                Fill Video Request Form
            </a>
            <a href="{{ route('order.index') }}" class="btn btn-info btn-lg w-100 text-white">
                User: View Orders
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-lg w-100">
                Admin: View Dashboard
            </a>
        </div>
    </div>
</body>
</html>