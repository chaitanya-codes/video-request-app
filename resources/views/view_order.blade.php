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
            <p class="alert alert-success">{{ session('success') }}</p>
        @elseif (session('error'))
            <p class="alert alert-danger">{{ session('error') }}</p>
        @endif
        @php
            $approved = json_decode($orderStatus->approved, true) ?? [
                'script' => false,
                'voiceover' => false,
                'segmentation' => false,
                'final_review' => false,
            ];

            function getStatusLabel($orderStatus, $limit, $path, $approvedScript)
            {
                if ($orderStatus->stage >= $limit && $approvedScript) {
                    return 'Completed';
                } elseif ($orderStatus->stage == $limit && !isset($orderStatus->$path)) {
                    return 'Pending';
                } elseif ($orderStatus->stage == $limit && !$approvedScript) {
                    return 'Awaiting approval';
                }
                return 'Pending';
            }
        @endphp

        <div class="card shadow-sm p-4">
            <h4 class="card-title">Order Progress</h4>
            <div class="parallelogram">
                @foreach ([
                            1 => ['label' => 'Script Generation', 'key' => 'script', 'path' => 'script_path'],
                            2 => ['label' => 'Voiceover Generation', 'key' => 'voiceover', 'path' => 'voiceover_path'],
                            3 => ['label' => 'Script Segments', 'key' => 'segment', 'path' => 'segments_path'],
                            4 => ['label' => 'Final Review', 'key' => 'final_review', 'path' => 'final_video_path'],
                        ] as $stage => $data)
                    <div class="stage {{ $orderStatus->stage > $stage ? 'stage-completed' : '' }}">
                        <div class="stage-label">{{ $data['label'] }}</div>
                        <div class="stage-content">
                            {{ getStatusLabel($orderStatus, $stage, $data['path'], $approved[$data['key']] ?? false) }}
                        </div>
                        @if (isset($orderStatus->{$data['path']}))
                            @if ($stage !== 3)
                                <a href="{{ route('order.view-file', ['id' => $order->id, 'path' => $data['path']]) }}"
                                    class="btn btn-info btn-sm">View File</a>
                            @else
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#segmentsModal">View Segments</button>
                            @endif
                            <form
                                action="{{ route('order.update-status', ['id' => $order->id, 'key' => $data['key'], 'path' => $data['path']]) }}"
                                method="POST" class="mt-2">
                                @csrf
                                <div class="btn-group d-flex">
                                    <button name="action" value="approve" class="btn btn-success btn-sm"
                                        {{ $approved[$data['key']] ? 'disabled' : '' }}>Approve</button>
                                    <button name="action" value="disapprove" class="btn btn-danger btn-sm"
                                        {{ $orderStatus->stage > $stage ? 'disabled' : '' }}>Disapprove</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="modal fade modal-scrollable" id="segmentsModal" tabindex="-1" aria-labelledby="segmentsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="segmentsModalLabel">Script Segments
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($orderStatus->segments_path)
                            @foreach ($orderStatus->segments_path as $segment)
                                <div class="mb-3">
                                    <strong>Segment {{ $loop->iteration }}:</strong>
                                    <a href="{{ route('order.view-file', ['id' => $order->id, 'path' => $segment]) }}" class="btn btn-info btn-sm">View File</a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm p-4 mt-4">
            <h4 class="card-title">Order Summary</h4>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Expected Delivery:</strong>
                    {{ $order->num_modules? $order->created_at->copy()->addDays($order->num_modules * 2)->format('d M Y'): 'N/A' }}
                </li>
                <li class="list-group-item"><strong>Video Name:</strong> {{ $order->video_name }}</li>
                <li class="list-group-item"><strong>Description:</strong> {{ $order->description }}</li>
                <li class="list-group-item"><strong>Orientation:</strong> {{ ucfirst($order->orientation) }}</li>
                <li class="list-group-item"><strong>Output Format:</strong> {{ strtoupper($order->output_format) }}
                </li>
                <li class="list-group-item"><strong>Avatar Gender:</strong> {{ ucfirst($order->avatar_gender) }}</li>
                <li class="list-group-item"><strong>Modules Count:</strong> {{ $order->num_modules }}</li>
                <li class="list-group-item"><strong>Expected Duration:</strong>
                    {{ $order->num_modules ? $order->num_modules * 3 . ' min' : 'N/A' }}</li>
                {{-- <li class="list-group-item"><strong>Logo:</strong> {!! $order->logo_path ? '<a href="' . route('admin.orders.view-logo', ['id' => $order->id]) . '">View Logo</a>' : 'N/A' !!}</li> --}}
                <li class="list-group-item"><strong>Brand Theme:</strong> {{ $order->brand_theme }}</li>
                <li class="list-group-item"><strong>Brand Color:</strong> <span
                        style="width: 20px; height: 20px; background-color: {{ $order->brand_color }}; display: inline-block;"></span>
                    {{ $order->brand_color }}</li>
                <li class="list-group-item"><strong>Design Notes:</strong> {{ $order->brand_design_notes }}</li>
                <li class="list-group-item"><strong>2D Animation Required:</strong>
                    {{ $order->animation_required ? 'Yes' : 'No' }}</li>
                <li class="list-group-item"><strong>Cost:</strong> ₹{{ $order->animation_required ? ($order->num_modules * 3) * 3000 : ($order->num_modules * 3) * 2400 }}</li>
                
            </ul>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
</body>

</html>
