<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Video Request Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ mix('css/review.css') }}" rel="stylesheet">
</head>
<body>
    @include('navbar')
    <div class="container">
        <div class="invoice-box">
            <div class="invoice-header d-flex justify-content-between align-items-center">
                <h2>Video Request Summary</h2>
                <small>{{ date('d M Y') }}</small>
            </div>
            <div class="invoice-subtitle">
                <p>Please review the details below and confirm your order.</p>
            @php
                $data['expected_duration'] = $data['num_modules'] * 3;
                $fields = [
                    'Video Name' => $data['video_name'],
                    'Description' => $data['description'],
                    'Orientation' => ucfirst($data['orientation']),
                    'Output Format' => strtoupper($data['output_format']),
                    'Avatar Gender' => ucfirst($data['avatar_gender']),
                    'Modules Count' => $data['num_modules'],
                    'Expected Duration' => $data['expected_duration'] ?? 'N/A',
                    'Brand Theme' => $data['brand_theme'],
                    'Design Notes' => $data['brand_design_notes'],
                    '2D Animation Required' => isset($data['animation_required']) ? 'Yes' : 'No',
                    'Expected Delivery' => $data['num_modules'] ? $data['num_modules'] * 2 . ' days' : 'N/A'
                ];
                $queryData = $data;
                unset($queryData['_token']);
                if (!isset($data['animation_required'])) {
                    $data['animation_required'] = 0;
                }
                $data['expected_cost'] = $data['animation_required'] ? $data['expected_duration'] * 3000 : $data['expected_duration'] * 2400;
                $data['advance_cost'] = $data['expected_cost'] * 30 / 100;
            @endphp

            @foreach ($fields as $label => $value)
                <div class="row mb-3">
                    <div class="col-sm-4 label">{{ $label }}:</div>
                    <div class="col-sm-8 value">{{ $value }}</div>
                </div>
            @endforeach

            <div class="hr-light"></div>

            <div class="summary-box">
                <div class="row mb-2">
                    <div class="col-sm-6 label">Advance Cost</div>
                    <div class="col-sm-6 text-end value">₹{{ $data['advance_cost'] ?? '0.00' }}</div>
                </div>
                <div class="row">
                    <div class="col-sm-6 label">Expected Total</div>
                    <div class="col-sm-6 text-end value">₹{{ $data['expected_cost'] ?? '0.00' }}</div>
                </div>
            </div>
            <div class="btn-group flex gap-5">
                <a href="{{ route('video-requests.create', $queryData) }}" class="btn btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('video-requests.place-order', ['edit' => $edit]) }}">
                    @csrf
                    @foreach ($data as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <button class="btn btn-primary">Proceed to Checkout</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>