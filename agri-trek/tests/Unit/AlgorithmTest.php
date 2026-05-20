<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Unit Test: K-Means clustering algorithm (pure PHP, no DB needed)
 */
class KMeansAlgorithmTest extends TestCase
{
    /**
     * Minimal inline K-Means to test independently of the controller.
     */
    private function runKMeans(array $points, int $k): array
    {
        $n = count($points);
        if ($n < $k) return [];

        $shuffled  = $points;
        shuffle($shuffled);
        $centroids = array_map(fn($p) => ['lat' => $p[0], 'lng' => $p[1]], array_slice($shuffled, 0, $k));
        $assignments = array_fill(0, $n, 0);

        for ($iter = 0; $iter < 20; $iter++) {
            $changed = false;
            foreach ($points as $i => $p) {
                $best = 0; $minD = PHP_FLOAT_MAX;
                foreach ($centroids as $j => $c) {
                    $d = sqrt(($p[0]-$c['lat'])**2 + ($p[1]-$c['lng'])**2);
                    if ($d < $minD) { $minD = $d; $best = $j; }
                }
                if ($assignments[$i] !== $best) { $assignments[$i] = $best; $changed = true; }
            }
            $sums = array_fill(0, $k, ['lat'=>0,'lng'=>0,'c'=>0]);
            foreach ($points as $i => $p) {
                $sums[$assignments[$i]]['lat'] += $p[0];
                $sums[$assignments[$i]]['lng'] += $p[1];
                $sums[$assignments[$i]]['c']++;
            }
            foreach ($centroids as $j => &$c) {
                if ($sums[$j]['c'] > 0) {
                    $c['lat'] = $sums[$j]['lat'] / $sums[$j]['c'];
                    $c['lng'] = $sums[$j]['lng'] / $sums[$j]['c'];
                }
            }
            if (!$changed) break;
        }

        $clusters = array_fill(0, $k, []);
        foreach ($points as $i => $p) $clusters[$assignments[$i]][] = $p;
        return $clusters;
    }

    /** @test */
    public function kmeans_produces_correct_number_of_clusters()
    {
        $points = array_map(fn($i) => [23.5 + $i*0.01, 72.5 + $i*0.01], range(0, 19));
        $clusters = $this->runKMeans($points, 4);
        $this->assertCount(4, $clusters);
    }

    /** @test */
    public function all_points_are_assigned_to_a_cluster()
    {
        $points = array_map(fn($i) => [23.5 + $i*0.005, 72.5 + $i*0.005], range(0, 14));
        $clusters = $this->runKMeans($points, 3);
        $total = array_sum(array_map('count', $clusters));
        $this->assertEquals(15, $total);
    }

    /** @test */
    public function kmeans_returns_empty_when_not_enough_points()
    {
        $points   = [[23.5, 72.5], [23.6, 72.6]];
        $clusters = $this->runKMeans($points, 5);
        $this->assertEmpty($clusters);
    }

    /** @test */
    public function clearly_separated_groups_cluster_correctly()
    {
        // Two distinct groups: one near (23.0, 72.0), another near (25.0, 75.0)
        $group1 = array_map(fn($i) => [23.0 + $i*0.001, 72.0 + $i*0.001], range(0, 9));
        $group2 = array_map(fn($i) => [25.0 + $i*0.001, 75.0 + $i*0.001], range(0, 9));
        $points = array_merge($group1, $group2);

        $clusters = $this->runKMeans($points, 2);

        // Each cluster should have 10 points
        $sizes = array_map('count', $clusters);
        sort($sizes);
        $this->assertEquals([10, 10], $sizes);
    }
}

/**
 * Unit Test: Sensor Fusion weighted average calculation
 */
class SensorFusionTest extends TestCase
{
    /**
     * Simplified sensor fusion: weighted average of sensor readings.
     * Weights: GPS=0.4, Speed=0.25, Altimeter=0.20, Camera=0.15
     */
    private function fusePosition(float $gpsLat, float $camLat, float $speedLat, float $altLat): float
    {
        $weights = ['gps' => 0.40, 'cam' => 0.15, 'speed' => 0.25, 'alt' => 0.20];
        return ($gpsLat * $weights['gps'])
             + ($camLat * $weights['cam'])
             + ($speedLat * $weights['speed'])
             + ($altLat * $weights['alt']);
    }

    /** @test */
    public function fusion_weights_sum_to_one()
    {
        $weights = [0.40, 0.15, 0.25, 0.20];
        $this->assertEquals(1.0, array_sum($weights));
    }

    /** @test */
    public function fused_value_is_between_min_and_max_sensor_readings()
    {
        $gpsLat   = 23.5120;
        $camLat   = 23.5118;
        $speedLat = 23.5122;
        $altLat   = 23.5119;

        $fused = $this->fusePosition($gpsLat, $camLat, $speedLat, $altLat);

        $min = min($gpsLat, $camLat, $speedLat, $altLat);
        $max = max($gpsLat, $camLat, $speedLat, $altLat);

        $this->assertGreaterThanOrEqual($min, $fused);
        $this->assertLessThanOrEqual($max, $fused);
    }

    /** @test */
    public function fused_result_is_closer_to_gps_due_to_higher_weight()
    {
        $gpsLat   = 23.5120;
        $others   = 23.5000; // far from GPS
        $fused    = $this->fusePosition($gpsLat, $others, $others, $others);

        // Fused should be closer to GPS than to others
        $distToGps    = abs($fused - $gpsLat);
        $distToOthers = abs($fused - $others);

        $this->assertLessThan($distToOthers, $distToGps);
    }

    /** @test */
    public function when_all_sensors_agree_fusion_returns_same_value()
    {
        $val   = 23.5120;
        $fused = $this->fusePosition($val, $val, $val, $val);
        $this->assertEqualsWithDelta($val, $fused, 0.000001);
    }
}
