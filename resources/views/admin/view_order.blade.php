<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>View Order (ID: {{ $order->id }})</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    @vite('resources/css/admin/viewOrder.css')
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">View Order</h1>
        <h4 class="text-center">{{ $order->video_name }}</h4>
        @if (session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
        @elseif (session('error'))
            <div class="alert alert-danger mt-3">
                {{ session('error') }}
            </div>
        @endif
        @php
            $approved = json_decode($orderStatus->approved, true) ?? [
                'script' => false,
                'voiceover' => false,
                'segmentation' => false,
                'final_review' => false,
            ];
            function getStatusLabel($orderStatus, $limit, $path, $approvedScript) {
                if ($orderStatus->stage > $limit && $approvedScript) return 'Completed';
                else if ($orderStatus->stage == $limit && !isset($orderStatus->$path)) return 'Pending';
                else if ($orderStatus->stage == $limit && !$approvedScript) return 'Awaiting approval';
                return 'Pending';
            }
        @endphp
        <div class="card shadow-sm p-4">
            <h4 class="card-title">Order Status</h4>
            <div class="parallelogram">
                <div class="stage {{ $orderStatus->stage > 1 ? 'stage-completed' : '' }}">
                    <div class="stage-label">Script Generation</div>
                    <div class="stage-content">
                        {{ getStatusLabel($orderStatus, 1, 'script_path', $approved['script']) }}
                        @if ($orderStatus->stage > 1 && $approved['script'])
                            {{-- <a href="{{ route('admin.orders.view-script', ['id' => $order->id]) }}" class="btn btn-secondary btn-sm">View Script</a> --}}
                        @endif
                    </div>
                </div>
                <div class="stage {{ $orderStatus->stage > 2 ? 'stage-completed' : '' }}">
                    <div class="stage-label">Voiceover Generation</div>
                    <div class="stage-content">
                        {{ getStatusLabel($orderStatus, 2, 'voiceover_path', $approved['voiceover']) }}
                    </div>
                </div>
                <div class="stage {{ $orderStatus->stage > 3 ? 'stage-completed' : '' }}">
                    <div class="stage-label">Script Segments</div>
                    <div class="stage-content">
                        {{ getStatusLabel($orderStatus, 3, 'segments_path', $approved['segment']) }}
                    </div>
                </div>
                <div class="stage {{ $orderStatus->stage == 4 && isset($orderStatus->final_video_path) ? 'stage-completed' : '' }}">
                    <div class="stage-label">Final Review</div>
                    <div class="stage-content">
                        {{ getStatusLabel($orderStatus, 4, 'final_video_path', $approved['final_review']) }}
                    </div>
                </div>
            </div>
        </div>
        @if (getStatusLabel($orderStatus, 1, 'script_path', $approved['script']) == 'Pending' && $orderStatus->stage == 1)
            <div class="card shadow-sm p-4 mb-4">
                <h5 class="card-title">Upload Script</h5>
                <form action="{{ route('admin.orders.update-status', ['id' => $order->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="script_file" class="form-control mb-2" accept=".pdf,.doc,.docx"/>
                    <button type="submit" class="btn btn-primary">Upload Script</button>
                </form>
            </div>
        @elseif (getStatusLabel($orderStatus, 2, 'voiceover_path', $approved['voiceover']) == 'Pending' && $orderStatus->stage == 2)
            <div class="card shadow-sm p-4 mb-4">
                <h5 class="card-title">Upload Voiceover</h5>
                <form action="{{ route('admin.orders.update-status', ['id' => $order->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="voiceover_file" class="form-control mb-2" accept=".mp3,.wav"/>
                    <button type="submit" class="btn btn-primary">Upload Voiceover</button>
                </form>
            </div>
        @elseif (getStatusLabel($orderStatus, 3, 'segments_path', $approved['segment']) == 'Pending' && $orderStatus->stage == 3)
            <div class="card shadow-sm p-4 mb-4">
                <h5 class="card-title">Upload Script Segments</h5>
                <form action="{{ route('admin.orders.update-status', ['id' => $order->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="segment_file" class="form-control mb-2" accept=".mp4,.mov,.avi,.wmv,.scorm"/>
                    <button type="submit" class="btn btn-primary">Upload Segment</button>
                </form>
            </div>
        @elseif (!isset($orderStatus->final_video_path) && (int)$orderStatus->stage == 4)
            <div class="card shadow-sm p-4 mb-4">
                <h5 class="card-title">Upload Final Review</h5>
                <form action="{{ route('admin.orders.update-status', ['id' => $order->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="final_video_file" class="form-control mb-2" accept=".mp4,.mov,.avi,.wmv,.scorm"/>
                    <button type="submit" class="btn btn-primary">Upload Final Review</button>
                </form>
            </div>
        @endif
        <div class="card shadow-sm p-4 mt-4">
            <h4 class="card-title">Order Details</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bold"><strong>Expected Delivery:</strong> {{ $order->num_modules ? $order->created_at->copy()->addDays($order->num_modules * 2)->format('d M Y') : 'N/A' }}</li>
                    <li class="list-group-item"><strong>Video Name:</strong> {{ $order->video_name }}</li>
                    <li class="list-group-item"><strong>Description:</strong> {{ $order->description }}</li>
                    <li class="list-group-item"><strong>Orientation:</strong> {{ ucfirst($order->orientation) }}</li>
                    <li class="list-group-item"><strong>Output Format:</strong> {{ strtoupper($order->output_format) }}
                    </li>
                    <li class="list-group-item"><strong>Avatar Gender:</strong> {{ ucfirst($order->avatar_gender) }}
                    </li>
                    <li class="list-group-item"><strong>Modules Count:</strong> {{ $order->num_modules }}</li>
                    <li class="list-group-item"><strong>Expected Duration:</strong>
                        {{ $order->num_modules ? $order->num_modules * 3 . ' min' : 'N/A' }}</li>
                    <li class="list-group-item"><strong>Logo:</strong> {!! $order->logo_path ? '<a href=' . route('admin.orders.view-logo', ['id' => $order->id]) . '>View Logo</a>' : 'N/A' !!}</li>
                    <li class="list-group-item"><strong>Brand Theme:</strong> {{ $order->brand_theme }}</li>
                    <li class="list-group-item"><strong>Brand Color:</strong> <span
                            style="width: 20px; height: 20px; background-color: {{ $order->brand_color }}; display: inline-block;"></span>
                        {{ $order->brand_color }}</li>
                    <li class="list-group-item"><strong>Design Notes:</strong> {{ $order->brand_design_notes }}</li>
                    <li class="list-group-item"><strong>2D Animation Required:</strong>
                        {{ $order->animation_required ? 'Yes' : 'No' }}</li>
                </ul>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
</body>
</html>
