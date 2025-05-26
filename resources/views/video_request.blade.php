<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Video Production Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ mix('css/form.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Request a Video</h1>
        @if (session('success'))
            <p style="color: green">{{ session('success') }}</p>
        @elseif (session('error'))
            <p style="color: red">{{ session('error') }}</p>
        @endif
        <form method="POST" action="{{ route('video-requests.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-floating mb-3">
                <input type="text" name="video_name" class="form-control" value="{{ request('video_name') }}" placeholder="Video Name" required>
                <label class="form-label">Video Name</label>
            </div>
            <div class="form-floating mb-3">
                <textarea name="description" class="form-control" rows="4" style="height: 15vh; max-height: 20vh" placeholder="Description" required>{{ request('description') }}</textarea>
                <label class="form-label">Video Description</label>
            </div>
            <div class="mb-3">
                <label class="form-label">Orientation</label>
                <select name="orientation" class="form-select" required>
                    <option value="" selected>Select Orientation</option>
                    <option value="landscape" {{ request('orientation') === 'landscape' ? 'selected' : '' }}>Landscape</option>
                    <option value="portrait" {{ request('orientation') === 'portrait' ? 'selected' : '' }}>Portrait</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Output Format</label>
                <select name="output_format" class="form-select" required>
                    <option value="" selected>Select format</option>
                    <option value="mp4" {{ request('output_format') === 'mp4' ? 'selected' : '' }}>MP4</option>
                    <option value="scorm" {{ request('output_format') === 'scorm' ? 'selected' : '' }}>SCORM</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Avatar Gender</label>
                <div class="form-check" required>
                    <input type="radio" class="btn-check" name="avatar_gender" value="male" id="male" required {{ request('avatar_gender') === 'male' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary form-check-label" for="male">Male</label>
                    <input type="radio" class="btn-check" name="avatar_gender" value="female" id="female" required {{ request('avatar_gender') === 'female' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary form-check-label" for="female">Female</label>
                    <div class="invalid-feedback">
                        Please select at least one option.
                    </div>
                </div>
            </div>
            <div class="form-floating mb-3">
                <input type="number" name="num_modules" class="form-control" min="1" max="30" placeholder="Number of Modules" required value="{{ request('num_modules') }}">
                <label class="form-label">Number of Modules</label>
                    <div class="invalid-feedback">
                        Please enter a value below 30
                    </div>
                <div class="video-info">
                    <span style="color: green" id="expected_duration_label"></span>
                    <input name="expected_duration" readonly>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Logo Path (Upload)</label>
                <input type="file" name="logo_path" class="form-control" accept="image/*" value="{{ request('logo_path') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Brand Color</label>
                <input type="color" name="brand_color" class="form-control" required value="{{ request('brand_color') }}">
            </div>
            <div class="form-floating mb-3">
                <input type="text" name="brand_theme" placeholder="Brand Theme" class="form-control" value="{{ request('brand_theme') }}">
                <label class="form-label">Brand Theme</label>
            </div>
            <div class="form-floating mb-3">
                <textarea name="brand_design_notes" class="form-control" rows="3" style="max-height: 15vh" placeholder="Brand Design Notes">{{ request('brand_design_notes') }}</textarea>
                <label class="form-label">Brand Design Notes</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input name="animation_required" id="animation_required" role="switch" type="checkbox" class="form-check-input" {{ request('animation_required') ? 'checked' : '' }}>
                <label class="form-check-label" for="animation_required">2D Animation / Graphics</label>
            </div>
            <div class="submission">
                <button type="submit" class="btn btn-primary">Submit Video Request</button>
            </div>
        </form>
    </div>
    @vite("resources/js/videoRequest.js")

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>