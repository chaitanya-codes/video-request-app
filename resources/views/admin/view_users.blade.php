<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=`">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>View Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>
<body>
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
        <tbody>
            <ul>
                @foreach ($users as $user)
                    <li>{{ $user->name }} ({{ $user->email }}) - {{ $user->role }}</li>
                @endforeach
            </ul>
        </tbody>
    </table>
</body>
</html>