<?php

namespace App\Http\Controllers;

use App\Models\DroneLog;
use Illuminate\Http\Request;

class ClusteringController
{
    // Cluster colors for visualization
    private array $colors = [
        '#e53935', '#1e88e5', '#43a047', '#8e24aa',
        '#fb8c00', '#00acc1', '#6d4c41', '#039be5',
        '#c0ca33', '#546e7a',
    ];

    private array $zoneTypes = [
        'Crop Monitoring Zone', 'Boundary Patrol Zone', 'Hotspot Zone',
        'Irrigation Survey Zone', 'Pest Detection Zone',
    ];

    public function index()
    {
        $k              = session('cluster_k', 4);
        $clusters       = session('clusters', []);
        $totalPoints    = DroneLog::count();
        $clusterCount   = count($clusters);
        $hotspotCluster = !empty($clusters)
            ? 'C' . (collect($clusters)->sortByDesc(fn($c) => count($c['points']))->keys()->first() + 1)
            : 'N/A';

        return view('clustering.index', compact(
            'clusters', 'k', 'totalPoints', 'clusterCount', 'hotspotCluster'
        ) + ['coverageArea' => '~' . ($clusterCount * 12) . ' km²', 'iterations' => session('iterations', 0)]);
    }

    /**
     * Run K-Means clustering on drone trajectory points.
     */
    public function run(Request $request)
    {
        $k = (int) $request->input('k', 4);
        $k = max(2, min(10, $k));

        // Fetch all drone log coordinates
        $points = DroneLog::select('id', 'latitude', 'longitude', 'speed', 'altitude')
                           ->get()
                           ->toArray();

        if (count($points) < $k) {
            return redirect()->route('clustering.index')
                             ->with('error', 'Not enough data points for clustering. Need at least ' . $k . ' points.');
        }

        [$clusters, $iterations] = $this->kMeans($points, $k);

        // Store in session (for demo; in production, store in DB)
        session(['clusters' => $clusters, 'cluster_k' => $k, 'iterations' => $iterations, 'cluster_count' => count($clusters)]);

        return redirect()->route('clustering.index')
                         ->with('success', "K-Means clustering completed! Found {$k} clusters in {$iterations} iterations.");
    }

    /**
     * Simple K-Means algorithm using Euclidean distance on lat/lng.
     *
     * @param array $points   Array of ['latitude', 'longitude', ...]
     * @param int   $k        Number of clusters
     * @param int   $maxIter  Max iterations
     * @return array          [clusters array, iteration count]
     */
    private function kMeans(array $points, int $k, int $maxIter = 20): array
    {
        $n = count($points);

        // Step 1: Initialize centroids by random selection
        $shuffled  = $points;
        shuffle($shuffled);
        $centroids = array_slice($shuffled, 0, $k);
        $centroids = array_map(fn($p) => [
            'lat' => (float) $p['latitude'],
            'lng' => (float) $p['longitude'],
        ], $centroids);

        $assignments = array_fill(0, $n, 0);
        $iterations  = 0;

        for ($iter = 0; $iter < $maxIter; $iter++) {
            $iterations++;
            $changed = false;

            // Step 2: Assign each point to nearest centroid
            foreach ($points as $i => $point) {
                $minDist   = PHP_FLOAT_MAX;
                $bestCluster = 0;

                foreach ($centroids as $j => $centroid) {
                    $dist = $this->euclideanDistance(
                        (float)$point['latitude'], (float)$point['longitude'],
                        $centroid['lat'], $centroid['lng']
                    );
                    if ($dist < $minDist) {
                        $minDist     = $dist;
                        $bestCluster = $j;
                    }
                }

                if ($assignments[$i] !== $bestCluster) {
                    $assignments[$i] = $bestCluster;
                    $changed = true;
                }
            }

            // Step 3: Recalculate centroids
            $sums   = array_fill(0, $k, ['lat' => 0.0, 'lng' => 0.0, 'count' => 0]);
            foreach ($points as $i => $point) {
                $c = $assignments[$i];
                $sums[$c]['lat']   += (float)$point['latitude'];
                $sums[$c]['lng']   += (float)$point['longitude'];
                $sums[$c]['count'] += 1;
            }

            foreach ($centroids as $j => &$centroid) {
                if ($sums[$j]['count'] > 0) {
                    $centroid['lat'] = $sums[$j]['lat'] / $sums[$j]['count'];
                    $centroid['lng'] = $sums[$j]['lng'] / $sums[$j]['count'];
                }
            }
            unset($centroid);

            if (!$changed) break; // Converged
        }

        // Step 4: Build cluster result structure
        $clusterPoints = array_fill(0, $k, []);
        foreach ($points as $i => $point) {
            $clusterPoints[$assignments[$i]][] = $point;
        }

        $clusters = [];
        foreach ($centroids as $j => $centroid) {
            $pts      = $clusterPoints[$j];
            $speedSum = array_sum(array_column($pts, 'speed'));
            $altSum   = array_sum(array_column($pts, 'altitude'));
            $count    = count($pts);

            // Estimate cluster radius (max distance from centroid)
            $radius = 200;
            foreach ($pts as $p) {
                $d = $this->euclideanDistance(
                    (float)$p['latitude'], (float)$p['longitude'],
                    $centroid['lat'], $centroid['lng']
                );
                // Convert degrees to approx meters (1 deg ≈ 111km)
                $meters = $d * 111000;
                if ($meters > $radius) $radius = $meters;
            }

            $clusters[] = [
                'centroid'    => $centroid,
                'points'      => $pts,
                'color'       => $this->colors[$j % count($this->colors)],
                'avg_speed'   => $count > 0 ? round($speedSum / $count, 2) : 0,
                'avg_altitude'=> $count > 0 ? round($altSum / $count, 2) : 0,
                'zone_type'   => $this->zoneTypes[$j % count($this->zoneTypes)],
                'radius'      => min($radius, 2000), // cap at 2km
            ];
        }

        return [$clusters, $iterations];
    }

    /**
     * Euclidean distance between two GPS coordinates (in degrees).
     */
    private function euclideanDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return sqrt(pow($lat1 - $lat2, 2) + pow($lng1 - $lng2, 2));
    }
}
