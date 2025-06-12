<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order (ID: {{ $order->id }})</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ mix('css/admin/viewOrder.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <a href="{{ route('order.index') }}" class="btn btn-secondary">&larr; Go Back</a>
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
                'segment' => false,
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
            <h4 class="card-title mb-3">Order Progress</h4>
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
                                    class="btn btn-info btn-sm mt-1">View File</a>
                            @else
                                <button class="btn btn-info btn-sm mt-1" data-bs-toggle="modal"
                                    data-bs-target="#segmentsModal">View Segments</button>
                            @endif
                            <form action="{{ route('order.update-status', ['id' => $order->id, 'key' => $data['key'], 'path' => $data['path']]) }}" method="POST" class="mt-2">
                                @csrf
                                <div class="btn-group d-flex">
                                    <button name="action" value="approve" class="btn btn-success btn-sm"
                                        {{ $approved[$data['key']] ? 'disabled' : '' }}>Approve</button>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-key="{{ $data['key'] }}" data-path="{{ $data['path'] }}"
                                        {{ $orderStatus->stage > $stage ? 'disabled' : '' }}>
                                        Edit
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="modal fade" id="segmentsModal" tabindex="-1" aria-labelledby="segmentsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Script Segments</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($orderStatus->segments_path)
                            @foreach ($orderStatus->segments_path as $segment)
                                <div class="mb-3">
                                    <strong>Segment {{ $loop->iteration }}:</strong>
                                    <a href="{{ route('order.view-file', ['id' => $order->id, 'path' => $segment]) }}"
                                        class="btn btn-info btn-sm">View File</a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('order.update-status', ['id' => $order->id]) }}"
                    class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="key" id="editKey">
                        <input type="hidden" name="path" id="editPath">
                        <label for="reason" class="form-label">Reason for Edit:</label>
                        <textarea name="reason" id="reason" rows="4" class="form-control" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Submit Edit</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Order Summary --}}
        <div class="card shadow-sm p-4 mt-4">
            <h4 class="card-title">Order Summary</h4>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Expected Delivery:</strong>
                    {{ $order->num_modules? $order->created_at->copy()->addDays($order->num_modules * 2)->format('d M Y'): 'N/A' }}
                </li>
                <li class="list-group-item"><strong>Video Name:</strong> {{ $order->video_name }}</li>
                <li class="list-group-item"><strong>Description:</strong> {{ $order->description }}</li>
                <li class="list-group-item"><strong>Orientation:</strong> {{ ucfirst($order->orientation) }}</li>
                <li class="list-group-item"><strong>Output Format:</strong> {{ strtoupper($order->output_format) }}</li>
                <li class="list-group-item"><strong>Avatar Gender:</strong> {{ ucfirst($order->avatar_gender) }}</li>
                <li class="list-group-item"><strong>Modules Count:</strong> {{ $order->num_modules }}</li>
                <li class="list-group-item"><strong>Expected Duration:</strong>
                    {{ $order->num_modules ? $order->num_modules * 3 . ' min' : 'N/A' }}</li>
                <li class="list-group-item"><strong>Brand Theme:</strong> {{ $order->brand_theme }}</li>
                <li class="list-group-item"><strong>Primary Brand Color:</strong>
                    <span style="width: 20px; height: 20px; background-color: {{ $order->primary_brand_color }}; display: inline-block;"></span>
                    {{ $order->primary_brand_color }}
                </li>
                <li class="list-group-item"><strong>Secondary Brand Color 1:</strong>
                    <span style="width: 20px; height: 20px; background-color: {{ $order->secondary_1_brand_color }}; display: inline-block;"></span>
                    {{ $order->secondary_1_brand_color }}
                </li>
                <li class="list-group-item"><strong>Secondary Brand Color 2:</strong>
                    <span style="width: 20px; height: 20px; background-color: {{ $order->secondary_2_brand_color }}; display: inline-block;"></span>
                    {{ $order->secondary_2_brand_color }}
                </li>
                <li class="list-group-item"><strong>Design Notes:</strong> {{ $order->brand_design_notes }}</li>
                <li class="list-group-item"><strong>2D Animation Required:</strong>
                    {{ $order->animation_required ? 'Yes' : 'No' }}</li>
                <li class="list-group-item"><strong>Cost:</strong>
                    ₹{{ $order->animation_required ? $order->num_modules * 3 * 3000 : $order->num_modules * 3 * 2400 }}
                </li>
            </ul>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const key = button.getAttribute('data-key');
            const path = button.getAttribute('data-path');
            document.getElementById('editKey').value = key;
            document.getElementById('editPath').value = path;
        });
    </script>
</body>
</html>
