@extends('layouts.app')
@section('title', 'Computer Vision')
@section('page-title', 'Computer Vision – Crop Analysis')

@push('styles')
<style>
    .upload-zone {
        border: 2px dashed #4caf50; border-radius: 12px;
        padding: 36px; text-align: center; cursor: pointer;
        transition: 0.2s; background: #f9fbe7; position: relative;
    }
    .upload-zone:hover, .upload-zone.dragover { background: #f1f8e9; border-color: #2e7d32; }
    .detection-wrapper { position: relative; display: inline-block; max-width: 100%; }
    .detection-wrapper img { max-width: 100%; border-radius: 8px; display: block; }
    .det-box {
        position: absolute; border: 3px solid; border-radius: 4px;
        box-sizing: border-box;
    }
    .det-label {
        position: absolute; top: -22px; left: 0;
        font-size: 10px; font-weight: 700; padding: 2px 7px;
        border-radius: 4px; white-space: nowrap; color: #fff;
    }
    .pipeline-step {
        background: #f8f9fa; border-radius: 10px; padding: 12px;
        border-left: 4px solid #4caf50; font-size: 13px;
    }
    .badge-analysis { font-size: 10px; padding: 3px 8px; border-radius: 20px; }
    .ndvi-bar { height: 10px; border-radius: 5px; background: linear-gradient(to right, #f44336, #ff9800, #ffeb3b, #4caf50, #1b5e20); }
    #previewBox { display: none; }
</style>
@endpush

@section('content')

{{-- ── HOW IT WORKS BANNER ─────────────────────────────────────────── --}}
<div class="alert border-0 mb-4" style="background:linear-gradient(135deg,#e8f5e9,#f1f8e9);border-left:4px solid #4caf50!important;border-radius:12px;">
    <div class="d-flex gap-3 align-items-start">
        <i class="bi bi-info-circle-fill text-success fs-4 mt-1"></i>
        <div>
            <strong class="text-success">How Drone Computer Vision Works</strong>
            <p class="mb-1 mt-1" style="font-size:13px;">
                In a real deployment, the drone's onboard camera captures images every few seconds at 50–150m altitude.
                These frames are transmitted to the server via 4G/LTE, where a <strong>YOLOv8 object detection model</strong>
                identifies crop zones, weeds, and diseases by analyzing pixel color distributions and vegetation indices (NDVI).
                Each detected region gets a <strong>bounding box + confidence score + class label</strong>.
            </p>
            @if(isset($gdAvailable) && $gdAvailable)
            <span class="badge bg-success"><i class="bi bi-cpu me-1"></i>GD Image Library: Active — Real pixel analysis enabled</span>
            @else
            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>GD Library not loaded — Using simulation mode</span>
            @endif
        </div>
    </div>
</div>

<div class="row g-3">
{{-- ── LEFT: Upload & Controls ─────────────────────────────────────── --}}
<div class="col-md-5">

    {{-- Upload Card --}}
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <i class="bi bi-camera-fill me-2"></i>Image Upload & Analysis
        </div>
        <div class="card-body">

            {{-- Drop Zone --}}
            <div class="upload-zone" id="dropZone"
                 ondragover="event.preventDefault();this.classList.add('dragover')"
                 ondragleave="this.classList.remove('dragover')"
                 ondrop="handleDrop(event)"
                 onclick="document.getElementById('imgInput').click()">
                <i class="bi bi-cloud-arrow-up fs-1 text-success d-block mb-2"></i>
                <strong>Drop image here or click to upload</strong><br>
                <small class="text-muted">JPG · PNG · WEBP · max 8MB</small>
                <div id="fileName" class="mt-2 text-success fw-bold"></div>
            </div>
            <input type="file" id="imgInput" accept="image/*" style="display:none" onchange="previewFile(this)">

            {{-- Preview --}}
            <div id="previewBox" class="mt-3 text-center">
                <img id="previewImg" src="" class="img-fluid rounded" style="max-height:180px;">
                <div id="imgStats" class="mt-2" style="font-size:12px;color:#666;"></div>
            </div>

            <form id="visionForm" action="{{ route('vision.analyze') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="image" id="hiddenImg" style="display:none">

                <div class="mt-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">Detection Mode</label>
                    <select name="detection_mode" class="form-select form-select-sm">
                        <option value="crop_health">🌾 Crop Health Analysis</option>
                        <option value="weed_detection">🌿 Weed Detection</option>
                        <option value="water_stress">💧 Water Stress Detection</option>
                        <option value="field_segmentation">🗺️ Field Segmentation</option>
                        <option value="object_detection">🔍 Full Object Detection</option>
                        <option value="full_analysis">🧠 Full Analysis (All Classes)</option>
                    </select>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        Confidence Threshold: <strong id="confVal">0.60</strong>
                    </label>
                    <input type="range" name="confidence" class="form-range"
                           min="0.30" max="0.95" step="0.05" value="0.60"
                           oninput="document.getElementById('confVal').textContent=parseFloat(this.value).toFixed(2)">
                    <div class="d-flex justify-content-between" style="font-size:11px;color:#aaa;">
                        <span>← More detections</span><span>Fewer, precise →</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 mt-3" id="analyzeBtn">
                    <i class="bi bi-eye me-2"></i>Analyze Image
                </button>
            </form>

            {{-- Sample buttons --}}
            <div class="mt-3">
                <small class="text-muted fw-bold d-block mb-2">Try without uploading:</small>
                <div class="d-flex flex-wrap gap-1">
                    @foreach(['Wheat Field','Rice Paddy','Diseased Crop','Drought Stress'] as $s)
                    <button class="btn btn-sm btn-outline-success" onclick="useSample('{{ $s }}')">
                        {{ $s }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Detection Class Legend --}}
    <div class="card">
        <div class="card-header bg-dark text-white" style="font-size:13px;">
            <i class="bi bi-list-ul me-2"></i>Detection Classes
        </div>
        <div class="card-body py-2">
            @foreach([
                ['Healthy Crop','#4caf50','Vigorous green vegetation'],
                ['Diseased Crop','#f44336','Fungal/bacterial infection'],
                ['Weed Cluster','#ff9800','Invasive plant species'],
                ['Water Stressed','#2196f3','Insufficient soil moisture'],
                ['Pest Damage','#9c27b0','Insect/larvae damage'],
                ['Bare Soil','#795548','No crop coverage'],
                ['Waterlogged','#00bcd4','Excess water accumulation'],
                ['Nitrogen Deficiency','#ffeb3b','Low N — yellowing leaves'],
            ] as [$name,$color,$desc])
            <div class="d-flex align-items-center gap-2 py-1 border-bottom">
                <div style="width:14px;height:14px;background:{{$color}};border-radius:3px;flex-shrink:0;"></div>
                <div>
                    <div style="font-size:12px;font-weight:600;">{{ $name }}</div>
                    <div style="font-size:10px;color:#888;">{{ $desc }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── RIGHT: Results ──────────────────────────────────────────────── --}}
<div class="col-md-7">
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-grid me-2"></i>Detection Results</span>
            @if(isset($analysisResult))
            <span class="badge {{ $analysisResult['analysis_type'] === 'Real Pixel Analysis' ? 'bg-success' : 'bg-secondary' }}">
                <i class="bi bi-cpu me-1"></i>{{ $analysisResult['analysis_type'] }}
            </span>
            @endif
        </div>
        <div class="card-body">
            @if(isset($analysisResult))

            {{-- Summary Stats --}}
            <div class="row g-2 mb-3">
                <div class="col-4">
                    <div class="text-center p-2 bg-light rounded">
                        <div class="fw-bold fs-4 text-dark">{{ $analysisResult['object_count'] }}</div>
                        <small class="text-muted">Detections</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-center p-2 rounded" style="background:#e8f5e9;">
                        <div class="fw-bold fs-4 text-success">{{ $analysisResult['healthy_pct'] }}%</div>
                        <small class="text-muted">Healthy Area</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-center p-2 rounded" style="background:#ffebee;">
                        <div class="fw-bold fs-4 text-danger">{{ $analysisResult['affected_pct'] }}%</div>
                        <small class="text-muted">Affected Area</small>
                    </div>
                </div>
            </div>

            {{-- NDVI Indicator --}}
            <div class="mb-3">
                <div class="d-flex justify-content-between" style="font-size:12px;">
                    <span class="text-muted fw-semibold">NDVI Index (Vegetation Health)</span>
                    <strong>{{ $analysisResult['ndvi'] }}</strong>
                </div>
                <div class="ndvi-bar mt-1"></div>
                <div class="d-flex justify-content-between" style="font-size:10px;color:#aaa;margin-top:2px;">
                    <span>Bare Soil (0.0)</span><span>Moderate (0.4)</span><span>Dense Crop (1.0)</span>
                </div>
                @php
                    $ndvi = $analysisResult['ndvi'];
                    $ndviText = $ndvi < 0.2 ? 'Poor — bare soil or stressed vegetation' :
                               ($ndvi < 0.4 ? 'Moderate — sparse or stressed crops' :
                               ($ndvi < 0.6 ? 'Good — healthy crop coverage' : 'Excellent — dense healthy vegetation'));
                @endphp
                <small class="text-muted">{{ $ndviText }}</small>
            </div>

            {{-- Image with bounding boxes --}}
            <div class="mb-3 text-center">
                <div class="detection-wrapper" id="detWrapper">
                    <img src="{{ $analysisResult['image_url'] }}"
                         id="resultImg"
                         class="img-fluid rounded"
                         style="max-height:280px;"
                         onload="drawBoxes()"
                         onerror="this.src='https://placehold.co/600x400/2e7d32/fff?text=Field+Image'">
                    <div id="boxContainer"></div>
                </div>
            </div>

            {{-- Detections Table --}}
            <div class="table-responsive mb-3" style="max-height:220px;overflow-y:auto;">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="font-size:11px;">Class</th>
                            <th style="font-size:11px;">Confidence</th>
                            <th style="font-size:11px;">Area</th>
                            <th style="font-size:11px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analysisResult['detections'] as $det)
                        <tr>
                            <td>
                                <span class="badge" style="background:{{ $det['color'] }};font-size:11px;">
                                    {{ $det['label'] }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <div style="width:50px;height:6px;background:#eee;border-radius:3px;">
                                        <div style="width:{{ $det['confidence']*100 }}%;height:100%;background:{{ $det['color'] }};border-radius:3px;"></div>
                                    </div>
                                    <span style="font-size:12px;">{{ number_format($det['confidence']*100,1) }}%</span>
                                </div>
                            </td>
                            <td style="font-size:12px;">{{ $det['area_pct'] }}%</td>
                            <td style="font-size:11px;color:#666;">{{ $det['action'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Recommendation --}}
            <div class="alert alert-{{ $analysisResult['alert_type'] }} py-2 mb-0" style="border-radius:10px;font-size:13px;">
                <strong><i class="bi bi-lightbulb-fill me-1"></i>AI Recommendation:</strong>
                {{ $analysisResult['recommendation'] }}
            </div>

            {{-- Pixel analysis data (if real) --}}
            @if(!empty($analysisResult['pixel_data']))
            <div class="mt-3 p-3 rounded" style="background:#f8f9fa;font-size:12px;">
                <strong class="d-block mb-2"><i class="bi bi-cpu me-1"></i>Real Pixel Analysis Data</strong>
                <div class="row g-1">
                    @foreach([
                        ['Green Pixels', $analysisResult['pixel_data']['green_pct'].'%', '#4caf50'],
                        ['Brown/Soil',   $analysisResult['pixel_data']['brown_pct'].'%',  '#795548'],
                        ['Yellow Stress',$analysisResult['pixel_data']['yellow_pct'].'%', '#ff9800'],
                        ['Blue/Water',   $analysisResult['pixel_data']['blue_pct'].'%',   '#2196f3'],
                    ] as [$k,$v,$c])
                    <div class="col-6 d-flex align-items-center gap-1">
                        <div style="width:10px;height:10px;background:{{$c}};border-radius:2px;"></div>
                        <span>{{ $k }}: <strong>{{ $v }}</strong></span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @else
            {{-- Empty state --}}
            <div class="text-center py-5 text-muted">
                <i class="bi bi-camera fs-1 d-block mb-3 opacity-25"></i>
                <strong>No analysis yet</strong><br>
                <small>Upload a field image or try a sample, then click Analyze</small>
            </div>
            <div class="row g-2 mt-3">
                @foreach([
                    ['Crop Health','87% healthy detected','success','bi-check-circle'],
                    ['Weed Zones','3 clusters mapped','warning','bi-exclamation-triangle'],
                    ['Water Stress','2 dry zones found','info','bi-droplet'],
                    ['Soil Coverage','15% bare soil','secondary','bi-layers'],
                ] as [$t,$d,$c,$i])
                <div class="col-6">
                    <div class="d-flex align-items-center gap-2 p-2 border rounded">
                        <i class="bi {{ $i }} text-{{ $c }} fs-5"></i>
                        <div>
                            <div style="font-size:12px;font-weight:600;">{{ $t }}</div>
                            <div style="font-size:11px;color:#888;">{{ $d }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Past Analyses --}}
    @if($pastAnalyses->count())
    <div class="card mt-3">
        <div class="card-header bg-primary text-white" style="font-size:13px;">
            <i class="bi bi-clock-history me-2"></i>Recent Analysis History
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="font-size:11px;">Time</th>
                        <th style="font-size:11px;">Mode</th>
                        <th style="font-size:11px;">Detections</th>
                        <th style="font-size:11px;">Healthy</th>
                        <th style="font-size:11px;">Affected</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pastAnalyses as $a)
                    <tr>
                        <td style="font-size:11px;">{{ $a->created_at->format('d M H:i') }}</td>
                        <td><span class="badge bg-light text-dark" style="font-size:10px;">{{ str_replace('_',' ',ucfirst($a->mode)) }}</span></td>
                        <td>{{ $a->object_count }}</td>
                        <td><span class="text-success fw-bold">{{ $a->healthy_pct }}%</span></td>
                        <td><span class="text-danger fw-bold">{{ $a->affected_pct }}%</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
</div>
@endsection

@push('scripts')
<script>
// Pass detection data to JS for drawing boxes
const detections = {!! isset($analysisResult) ? json_encode($analysisResult['detections']) : '[]' !!};

function drawBoxes() {
    const img = document.getElementById('resultImg');
    const container = document.getElementById('boxContainer');
    if (!container || !detections.length) return;
    container.innerHTML = '';

    const rect = img.getBoundingClientRect();
    const wrapper = document.getElementById('detWrapper');

    detections.forEach(d => {
        const box = document.createElement('div');
        box.className = 'det-box';
        box.style.cssText = `
            left:${d.x}%;top:${d.y}%;width:${d.w}%;height:${d.h}%;
            border-color:${d.color};
        `;
        const lbl = document.createElement('div');
        lbl.className = 'det-label';
        lbl.style.background = d.color;
        lbl.textContent = `${d.label} ${Math.round(d.confidence*100)}%`;
        box.appendChild(lbl);
        container.appendChild(box);
    });
}
// Re-draw if image already loaded
window.addEventListener('load', drawBoxes);

// File preview
function previewFile(input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    document.getElementById('fileName').textContent = '📎 ' + file.name;
    document.getElementById('previewBox').style.display = '';
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('imgStats').textContent =
            `${file.name} · ${(file.size/1024).toFixed(0)} KB`;
    };
    reader.readAsDataURL(file);
    // Copy to hidden input
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('hiddenImg').files = dt.files;
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('imgInput').files = dt.files;
        previewFile(document.getElementById('imgInput'));
    }
}

function useSample(name) {
    document.getElementById('fileName').textContent = '🌾 Sample: ' + name;
    document.getElementById('previewBox').style.display = '';
    document.getElementById('previewImg').src = 'https://placehold.co/600x400/2e7d32/fff?text=' + encodeURIComponent(name);
    document.getElementById('imgStats').textContent = 'Sample field image for ' + name;
}

// Form submit feedback
document.getElementById('visionForm').addEventListener('submit', function() {
    const btn = document.getElementById('analyzeBtn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Analyzing...';
    btn.disabled = true;
});
</script>
@endpush
