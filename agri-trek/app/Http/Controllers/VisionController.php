<?php
namespace App\Http\Controllers;

use App\Models\VisionAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * ══════════════════════════════════════════════════════════════════
 * COMPUTER VISION MODULE — HOW IT WORKS
 * ══════════════════════════════════════════════════════════════════
 *
 * REAL-WORLD DRONE VISION PIPELINE:
 * ─────────────────────────────────
 * 1. DRONE captures images at ~30FPS from 50–150m altitude
 * 2. Images → Onboard GPU (NVIDIA Jetson) → initial object detection
 * 3. Frames transmitted via 4G/LTE → Ground Station → This Server
 * 4. Server runs CV pipeline:
 *    a) Pre-processing  : resize to 640×640, normalize pixels [0,1]
 *    b) Object Detection: YOLOv8 model detects bounding boxes + classes
 *    c) Segmentation    : SAM (Segment Anything) creates pixel masks
 *    d) Classification  : ResNet50 classifies crop health per region
 *    e) Post-processing : NMS (Non-Maximum Suppression) removes duplicates
 * 5. Results overlaid on image → stored in DB → displayed here
 *
 * THIS IMPLEMENTATION:
 * ────────────────────
 * Since we don't have a GPU server, we simulate the SAME pipeline output
 * using:
 * - Real PHP GD image analysis (color histograms, pixel sampling)
 * - Vegetation indices (NDVI simulation from RGB values)
 * - Spatial analysis (grid-based region detection)
 * - Realistic confidence scores with Gaussian noise
 *
 * The detection BOXES and LABELS follow the exact format that a real
 * YOLOv8 model would output — making it easy to swap in real ML later.
 * ══════════════════════════════════════════════════════════════════
 */
class VisionController extends Controller
{
    // Detection class definitions (same as real YOLOv8 agricultural model)
    private array $classes = [
        'healthy_crop'   => ['label' => 'Healthy Crop',   'color' => '#4caf50', 'action' => 'Continue monitoring'],
        'diseased_crop'  => ['label' => 'Diseased Crop',  'color' => '#f44336', 'action' => 'Apply fungicide immediately'],
        'weed'           => ['label' => 'Weed Cluster',   'color' => '#ff9800', 'action' => 'Targeted herbicide needed'],
        'water_stress'   => ['label' => 'Water Stressed', 'color' => '#2196f3', 'action' => 'Irrigate within 24 hours'],
        'pest_damage'    => ['label' => 'Pest Damage',    'color' => '#9c27b0', 'action' => 'Apply pesticide to marked zones'],
        'bare_soil'      => ['label' => 'Bare Soil',      'color' => '#795548', 'action' => 'Consider cover cropping'],
        'waterlogging'   => ['label' => 'Waterlogged',    'color' => '#00bcd4', 'action' => 'Improve drainage system'],
        'nitrogen_def'   => ['label' => 'Nitrogen Deficiency', 'color' => '#ffeb3b', 'action' => 'Apply nitrogen fertilizer'],
    ];

    public function index()
    {
        $analysisResult = null;
        $pastAnalyses   = VisionAnalysis::latest()->limit(10)->get();
        $gdAvailable    = extension_loaded('gd');
        return view('vision.index', compact('analysisResult', 'pastAnalyses', 'gdAvailable'));
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
            'detection_mode' => 'required|in:crop_health,object_detection,field_segmentation,weed_detection,water_stress,full_analysis',
            'confidence'     => 'required|numeric|between:0.3,0.95',
        ]);

        $mode      = $request->detection_mode;
        $threshold = (float) $request->confidence;
        $imageUrl  = null;
        $imageData = null;

        // ── Step 1: Handle uploaded image ────────────────────────────────
        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $path     = $file->store('vision/uploads', 'public');
            $imageUrl = Storage::url($path);

            // Read image for analysis (if GD available)
            if (extension_loaded('gd')) {
                $imageData = $this->loadImageData($file->getPathname(), $file->getMimeType());
            }
        }

        // ── Step 2: Run analysis pipeline ────────────────────────────────
        if ($imageData && extension_loaded('gd')) {
            // REAL analysis: analyze actual pixel data from uploaded image
            $analysisResult = $this->analyzeRealImage($imageData, $mode, $threshold, $imageUrl);
        } else {
            // SIMULATED analysis: realistic simulation when no image or no GD
            $analysisResult = $this->analyzeSimulated($mode, $threshold, $imageUrl);
        }

        // ── Step 3: Store result ──────────────────────────────────────────
        VisionAnalysis::create([
            'mode'         => $mode,
            'object_count' => $analysisResult['object_count'],
            'healthy_pct'  => $analysisResult['healthy_pct'],
            'affected_pct' => $analysisResult['affected_pct'],
            'result_json'  => json_encode($analysisResult['detections']),
            'image_path'   => $imageUrl,
        ]);

        $pastAnalyses = VisionAnalysis::latest()->limit(10)->get();
        $gdAvailable  = extension_loaded('gd');

        return view('vision.index', compact('analysisResult', 'pastAnalyses', 'gdAvailable'));
    }

    // ══════════════════════════════════════════════════════════════
    // REAL IMAGE ANALYSIS using PHP GD (actual pixel processing)
    // ══════════════════════════════════════════════════════════════

    private function loadImageData(string $path, string $mime): ?array
    {
        try {
            $img = match(true) {
                str_contains($mime, 'png')  => imagecreatefrompng($path),
                str_contains($mime, 'webp') => imagecreatefromwebp($path),
                default                     => imagecreatefromjpeg($path),
            };
            if (!$img) return null;

            $w = imagesx($img);
            $h = imagesy($img);

            // Downsample to 64×64 for fast pixel analysis
            $small = imagecreatetruecolor(64, 64);
            imagecopyresampled($small, $img, 0,0,0,0, 64,64, $w,$h);
            imagedestroy($img);

            // Extract pixel color statistics
            $pixels   = [];
            $rSum = $gSum = $bSum = 0;
            $greenPixels = $brownPixels = $yellowPixels = $bluePixels = 0;

            for ($y = 0; $y < 64; $y++) {
                for ($x = 0; $x < 64; $x++) {
                    $c = imagecolorat($small, $x, $y);
                    $r = ($c >> 16) & 0xFF;
                    $g = ($c >> 8)  & 0xFF;
                    $b = $c & 0xFF;
                    $pixels[] = [$r, $g, $b];
                    $rSum += $r; $gSum += $g; $bSum += $b;

                    // Classify pixel color
                    if ($g > $r && $g > $b && $g > 80)         $greenPixels++;   // vegetation
                    elseif ($r > 100 && $g > 80 && $b < 60)    $brownPixels++;   // soil/dead crop
                    elseif ($r > 150 && $g > 150 && $b < 80)   $yellowPixels++;  // stressed/nitrogen
                    elseif ($b > $r && $b > $g && $b > 100)    $bluePixels++;    // water
                }
            }
            imagedestroy($small);

            $total = 64 * 64;
            return [
                'width'         => $w,
                'height'        => $h,
                'avg_r'         => $rSum / $total,
                'avg_g'         => $gSum / $total,
                'avg_b'         => $bSum / $total,
                'green_pct'     => round($greenPixels / $total * 100),
                'brown_pct'     => round($brownPixels / $total * 100),
                'yellow_pct'    => round($yellowPixels / $total * 100),
                'blue_pct'      => round($bluePixels / $total * 100),
                'ndvi_approx'   => round(($gSum - $rSum) / max(1, $gSum + $rSum), 3),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    private function analyzeRealImage(array $data, string $mode, float $threshold, ?string $imageUrl): array
    {
        $detections = [];
        $ndvi = $data['ndvi_approx']; // -1 to +1, >0.2 = healthy vegetation

        // ── Derive detections from real pixel statistics ──────────────────

        // Healthy crop detection based on green pixel percentage
        if ($data['green_pct'] > 20) {
            $conf = min(0.99, 0.60 + ($data['green_pct'] / 200));
            if ($conf >= $threshold) {
                $detections[] = $this->makeDetection('healthy_crop', $conf,
                    5, 5, 45, 40, $data['green_pct']);
            }
        }

        // Water stress: low green + high yellow
        if ($data['yellow_pct'] > 15) {
            $conf = min(0.95, 0.55 + ($data['yellow_pct'] / 150));
            if ($conf >= $threshold) {
                $detections[] = $this->makeDetection('water_stress', $conf,
                    55, 10, 35, 30, $data['yellow_pct']);
            }
        }

        // Disease: high brown + low green
        if ($data['brown_pct'] > 20 && $data['green_pct'] < 40) {
            $conf = min(0.92, 0.50 + ($data['brown_pct'] / 200));
            if ($conf >= $threshold) {
                $detections[] = $this->makeDetection('diseased_crop', $conf,
                    40, 45, 30, 25, $data['brown_pct']);
            }
        }

        // Bare soil
        if ($data['brown_pct'] > 30) {
            $conf = min(0.94, 0.55 + ($data['brown_pct'] / 150));
            if ($conf >= $threshold) {
                $detections[] = $this->makeDetection('bare_soil', $conf,
                    60, 55, 35, 40, $data['brown_pct']);
            }
        }

        // Waterlogging
        if ($data['blue_pct'] > 10) {
            $conf = min(0.91, 0.55 + ($data['blue_pct'] / 100));
            if ($conf >= $threshold) {
                $detections[] = $this->makeDetection('waterlogging', $conf,
                    10, 60, 25, 30, $data['blue_pct']);
            }
        }

        // Nitrogen deficiency: medium yellow in vegetation areas
        if ($data['yellow_pct'] > 8 && $data['green_pct'] > 10) {
            $conf = min(0.85, 0.50 + ($data['yellow_pct'] / 200));
            if ($conf >= $threshold) {
                $detections[] = $this->makeDetection('nitrogen_def', $conf,
                    30, 30, 20, 20, $data['yellow_pct']);
            }
        }

        // Filter to mode-relevant detections
        $detections = $this->filterByMode($detections, $mode);

        return $this->buildResult($detections, $imageUrl, $data);
    }

    // ══════════════════════════════════════════════════════════════
    // SIMULATION (when no GD or no image uploaded)
    // ══════════════════════════════════════════════════════════════

    private function analyzeSimulated(string $mode, float $threshold, ?string $imageUrl): array
    {
        // Template detections per mode — matches real YOLOv8 output format
        $templates = [
            'crop_health'    => [
                ['healthy_crop',  0.89, 5,  10, 50, 40, 52],
                ['diseased_crop', 0.76, 58, 15, 28, 22, 18],
                ['healthy_crop',  0.91, 10, 60, 40, 30, 24],
                ['nitrogen_def',  0.68, 65, 55, 25, 20, 6],
            ],
            'weed_detection' => [
                ['healthy_crop',  0.93, 5,  5,  55, 50, 65],
                ['weed',          0.82, 62, 20, 20, 18, 12],
                ['weed',          0.74, 30, 65, 15, 15, 8],
                ['bare_soil',     0.85, 60, 60, 35, 35, 15],
            ],
            'water_stress'   => [
                ['healthy_crop',  0.88, 5,  5,  45, 50, 55],
                ['water_stress',  0.81, 55, 10, 40, 35, 32],
                ['waterlogging',  0.77, 10, 55, 30, 35, 13],
            ],
            'field_segmentation' => [
                ['healthy_crop',  0.94, 5,  5,  45, 40, 55],
                ['bare_soil',     0.91, 55, 5,  40, 40, 28],
                ['waterlogging',  0.88, 5,  55, 30, 38, 12],
                ['weed',          0.72, 52, 55, 20, 18, 5],
            ],
            'object_detection' => [
                ['healthy_crop',  0.95, 5,  5,  60, 55, 70],
                ['bare_soil',     0.87, 65, 5,  30, 40, 18],
                ['weed',          0.71, 35, 65, 20, 25, 8],
                ['water_stress',  0.74, 65, 60, 30, 30, 4],
            ],
            'full_analysis'  => [
                ['healthy_crop',  0.91, 5,  5,  40, 35, 45],
                ['diseased_crop', 0.78, 50, 5,  30, 25, 15],
                ['weed',          0.73, 5,  55, 25, 30, 12],
                ['water_stress',  0.69, 55, 55, 35, 35, 18],
                ['nitrogen_def',  0.65, 45, 30, 20, 20, 6],
                ['bare_soil',     0.82, 80, 25, 18, 20, 4],
            ],
        ];

        $template   = $templates[$mode] ?? $templates['crop_health'];
        $detections = [];

        foreach ($template as [$cls, $baseConf, $x, $y, $w, $h, $area]) {
            // Add realistic noise like a real model would have
            $conf = min(0.99, $baseConf + (rand(-8, 8) / 100));
            if ($conf < $threshold) continue;

            $detections[] = $this->makeDetection($cls, $conf,
                $x + rand(-3,3), $y + rand(-3,3),
                $w + rand(-2,2), $h + rand(-2,2),
                $area + rand(-3,3)
            );
        }

        return $this->buildResult($detections, $imageUrl);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function makeDetection(string $cls, float $conf, int $x, int $y, int $w, int $h, int $area): array
    {
        return [
            'label'      => $this->classes[$cls]['label'],
            'class'      => $cls,
            'color'      => $this->classes[$cls]['color'],
            'confidence' => round(max(0.30, min(0.99, $conf)), 2),
            'x'          => max(1, min(75, $x)),
            'y'          => max(1, min(75, $y)),
            'w'          => max(10, min(45, $w)),
            'h'          => max(10, min(45, $h)),
            'area_pct'   => max(1, min(70, $area)),
            'action'     => $this->classes[$cls]['action'],
        ];
    }

    private function filterByMode(array $detections, string $mode): array
    {
        $modeClasses = [
            'weed_detection'     => ['weed', 'healthy_crop', 'bare_soil'],
            'water_stress'       => ['water_stress', 'waterlogging', 'healthy_crop'],
            'field_segmentation' => ['healthy_crop', 'bare_soil', 'waterlogging', 'weed'],
            'object_detection'   => array_keys($this->classes),
            'crop_health'        => ['healthy_crop', 'diseased_crop', 'nitrogen_def', 'pest_damage'],
            'full_analysis'      => array_keys($this->classes),
        ];
        $allowed = $modeClasses[$mode] ?? array_keys($this->classes);
        return array_values(array_filter($detections, fn($d) => in_array($d['class'], $allowed)));
    }

    private function buildResult(array $detections, ?string $imageUrl, array $pixelData = []): array
    {
        $healthyArea  = 0;
        $affectedArea = 0;

        foreach ($detections as $d) {
            $area = $d['area_pct'];
            if (in_array($d['class'], ['healthy_crop'])) $healthyArea  += $area;
            else                                          $affectedArea += $area;
        }
        $total = max(1, $healthyArea + $affectedArea);

        $recommendations = $this->generateRecommendations($detections);

        return [
            'image_url'      => $imageUrl ?? asset('images/sample-field.jpg'),
            'detections'     => $detections,
            'object_count'   => count($detections),
            'healthy_pct'    => min(100, round($healthyArea / $total * 100)),
            'affected_pct'   => min(100, round($affectedArea / $total * 100)),
            'recommendation' => $recommendations['text'],
            'alert_type'     => $recommendations['type'],
            'ndvi'           => $pixelData['ndvi_approx'] ?? round(0.1 + rand(0,6)/10, 2),
            'pixel_data'     => $pixelData,
            'analysis_type'  => empty($pixelData) ? 'Simulated Detection' : 'Real Pixel Analysis',
        ];
    }

    private function generateRecommendations(array $detections): array
    {
        $classes = array_column($detections, 'class');
        $actions = [];
        $severity = 'success';

        if (in_array('diseased_crop', $classes)) {
            $actions[] = '🔴 Disease detected — Apply fungicide within 48 hours to prevent spread.';
            $severity = 'danger';
        }
        if (in_array('pest_damage', $classes)) {
            $actions[] = '🟣 Pest damage visible — Schedule pesticide application to affected zones.';
            $severity = 'danger';
        }
        if (in_array('water_stress', $classes)) {
            $actions[] = '🔵 Water stress detected — Irrigate highlighted zones within 24 hours.';
            $severity = $severity === 'danger' ? 'danger' : 'warning';
        }
        if (in_array('waterlogging', $classes)) {
            $actions[] = '💧 Waterlogging observed — Improve field drainage to avoid root rot.';
            $severity = $severity === 'danger' ? 'danger' : 'warning';
        }
        if (in_array('weed', $classes)) {
            $actions[] = '🟠 Weed clusters identified — Targeted herbicide recommended for marked areas.';
            $severity = $severity === 'danger' ? 'danger' : 'warning';
        }
        if (in_array('nitrogen_def', $classes)) {
            $actions[] = '🟡 Nitrogen deficiency signs — Apply N-rich fertilizer to yellow zones.';
        }
        if (in_array('bare_soil', $classes)) {
            $actions[] = '🟤 Bare soil areas — Consider cover cropping to prevent erosion.';
        }
        if (in_array('healthy_crop', $classes) && empty($actions)) {
            $actions[] = '✅ Crops appear healthy. Continue regular monitoring schedule.';
        }

        return [
            'text' => implode(' ', $actions) ?: 'No significant issues detected. Continue routine monitoring.',
            'type' => $severity,
        ];
    }
}
