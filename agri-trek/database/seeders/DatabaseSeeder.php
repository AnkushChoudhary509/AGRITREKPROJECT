<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Farmer;
use App\Models\Land;
use App\Models\Scheme;
use App\Models\SchemeApplication;
use App\Models\Drone;
use App\Models\DroneLog;
use App\Models\Waypoint;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding Agri-Trek database...');

        // ── Users ──────────────────────────────────────────────────────
        $admin = User::create([
            'name'           => 'Admin User',
            'email'          => 'admin@agritrek.com',
            'password'       => Hash::make('password'),
            'role'           => 'admin',
            'is_active'      => true,
            'email_verified' => true,
            'organization'   => 'Agri-Trek HQ',
        ]);

        $expert = User::create([
            'name'           => 'Ankush (Expert)',
            'email'          => 'ankushnagokay4631@gmail.com',
            'password'       => Hash::make('AnkushJatt23@aR'),
            'role'           => 'expert',
            'is_active'      => true,
            'email_verified' => true,
            'organization'   => 'Agri-Trek Expert Team',
        ]);

        // ── Farmers ────────────────────────────────────────────────────
        $farmersData = [
            ['Ramesh Patel',  '9876543210', 'Anand Nagar',  'Anand',    'Anand'],
            ['Suresh Kumar',  '9876543211', 'MG Road',      'Nadiad',   'Kheda'],
            ['Priya Sharma',  '9876543212', 'Civil Lines',  'Mehsana',  'Mehsana'],
            ['Dinesh Yadav',  '9876543213', 'Station Road', 'Unjha',    'Mehsana'],
            ['Lakshmi Devi',  '9876543214', 'Patel Street', 'Vijapur',  'Mehsana'],
            ['Mohan Singh',   '9876543215', 'Gandhi Chowk', 'Kadi',     'Mehsana'],
            ['Anita Verma',   '9876543216', 'NH-48',        'Kalol',    'Gandhinagar'],
            ['Vijay Patil',   '9876543217', 'Market Road',  'Deesa',    'Banaskantha'],
            ['Sunita Joshi',  '9876543218', 'Near River',   'Patan',    'Patan'],
            ['Rajan Mehta',   '9876543219', 'Old Town',     'Palanpur', 'Banaskantha'],
        ];

        $farmers = [];
        foreach ($farmersData as $i => $f) {
            $farmer = Farmer::create([
                'name' => $f[0], 'mobile' => $f[1], 'address' => $f[2],
                'village' => $f[3], 'district' => $f[4],
                'aadhaar' => str_pad($i + 1, 12, '0', STR_PAD_LEFT),
            ]);
            $farmers[] = $farmer;

            // Create farmer login account for first farmer
            if ($i === 0) {
                User::create([
                    'name'           => $farmer->name,
                    'email'          => 'farmer@agritrek.com',
                    'password'       => Hash::make('password'),
                    'role'           => 'farmer',
                    'farmer_id'      => $farmer->id,
                    'is_active'      => true,
                    'email_verified' => true,
                ]);
            }
        }

        // ── Lands ──────────────────────────────────────────────────────
        $crops  = ['Wheat','Rice','Cotton','Sugarcane','Maize','Groundnut','Soybean','Mustard'];
        $soils  = ['Clay','Sandy','Loamy','Silty','Black Cotton'];
        $irrs   = ['Canal','Drip','Sprinkler','Rainfed','Borewell'];
        $baseLat = 23.5; $baseLng = 72.5;

        foreach ($farmers as $fi => $farmer) {
            for ($l = 0; $l < rand(1,3); $l++) {
                Land::create([
                    'farmer_id'       => $farmer->id,
                    'area'            => rand(1,15) + (rand(0,9)/10),
                    'soil_type'       => $soils[array_rand($soils)],
                    'crop_type'       => $crops[($fi+$l) % count($crops)],
                    'latitude'        => $baseLat + (rand(-50,50)/100),
                    'longitude'       => $baseLng + (rand(-50,50)/100),
                    'irrigation_type' => $irrs[array_rand($irrs)],
                    'survey_number'   => 'SY-'.rand(100,999),
                ]);
            }
        }

        // ── Schemes ────────────────────────────────────────────────────
        $schemes = [];
        foreach ([
            ['PM Kisan Samman Nidhi', 6000,  'Small & marginal farmers owning up to 2 hectares', 'Ministry of Agriculture'],
            ['PM Fasal Bima Yojana',  15000, 'All farmers growing notified crops',                'Ministry of Agriculture'],
            ['Micro Irrigation Fund', 8000,  'Farmers switching to drip/sprinkler irrigation',   'NABARD'],
            ['Soil Health Card',      500,   'All farmers for soil testing',                      'State Agriculture Dept'],
            ['RKVY Scheme',           25000, 'Farmers with 5+ acres of agricultural land',        'Ministry of Agriculture'],
            ['Kisan Credit Card',     50000, 'All farmers with valid land documents',             'NABARD & Banks'],
        ] as $s) {
            $schemes[] = Scheme::create([
                'name' => $s[0], 'subsidy_amount' => $s[1],
                'eligibility' => $s[2], 'department' => $s[3],
                'description' => 'Government scheme to support Indian farmers with financial assistance.',
                'start_date' => now()->subMonths(rand(1,6)),
                'end_date'   => now()->addMonths(rand(6,18)),
                'is_active'  => true,
            ]);
        }

        // Applications
        $statuses = ['pending','approved','rejected','pending','approved'];
        foreach ($farmers as $fi => $farmer) {
            $applied = [];
            for ($a = 0; $a < rand(1,3); $a++) {
                $scheme = $schemes[rand(0,count($schemes)-1)];
                if (in_array($scheme->id,$applied)) continue;
                $applied[] = $scheme->id;
                SchemeApplication::create([
                    'farmer_id'   => $farmer->id,
                    'scheme_id'   => $scheme->id,
                    'status'      => $statuses[rand(0,4)],
                    'applied_date'=> now()->subDays(rand(1,90)),
                ]);
            }
        }

        // ── Drones ─────────────────────────────────────────────────────
        $drones = [];
        foreach ([
            ['AgriHawk-01','DRONE-001','DJI Agras T30',   'active'],
            ['SkyEye-02',  'DRONE-002','DJI Phantom 4',   'active'],
            ['FieldBot-03','DRONE-003','Parrot Bluegrass', 'active'],
            ['CropScan-04','DRONE-004','senseFly eBee',    'idle'],
            ['TerraView-05','DRONE-005','AgEagle RX-60',   'offline'],
        ] as $d) {
            $drones[] = Drone::create([
                'name'=>$d[0],'drone_id'=>$d[1],'model'=>$d[2],'status'=>$d[3],
                'description'=>'Agricultural surveillance drone.',
            ]);
        }

        // Drone logs — generate realistic sinusoidal paths
        foreach ($drones as $di => $drone) {
            if ($drone->status === 'offline') continue;
            $lat = $baseLat + ($di * 0.05);
            $lng = $baseLng + ($di * 0.05);
            for ($t = 0; $t < 30; $t++) {
                $lat += 0.001 * cos(deg2rad($t * 12));
                $lng += 0.001 * sin(deg2rad($t * 12));
                DroneLog::create([
                    'drone_id'   => $drone->id,
                    'latitude'   => round($lat, 7),
                    'longitude'  => round($lng, 7),
                    'speed'      => rand(20, 65),
                    'altitude'   => rand(40, 150),
                    'direction'  => ($t * 12) % 360,
                    'created_at' => now()->subMinutes((30-$t)*5),
                    'updated_at' => now()->subMinutes((30-$t)*5),
                ]);
            }
        }

        // ── Waypoints ──────────────────────────────────────────────────
        foreach ([
            ['North Field Survey', $drones[0]->id, [
                [23.51,72.48,1],[23.52,72.49,2],[23.53,72.50,3],
                [23.53,72.52,4],[23.52,72.53,5],[23.51,72.52,6],
            ]],
            ['South Field Patrol', $drones[1]->id, [
                [23.48,72.48,1],[23.47,72.50,2],[23.46,72.52,3],
                [23.47,72.54,4],[23.48,72.55,5],
            ]],
            ['Perimeter Check', $drones[2]->id, [
                [23.50,72.45,1],[23.55,72.45,2],[23.55,72.55,3],
                [23.50,72.55,4],[23.50,72.45,5],
            ]],
        ] as $route) {
            foreach ($route[2] as $i => $pt) {
                Waypoint::create([
                    'name'       => $route[0].' WP-'.$pt[2],
                    'route_name' => $route[0],
                    'drone_id'   => $route[1],
                    'latitude'   => $pt[0],
                    'longitude'  => $pt[1],
                    'sequence'   => $pt[2],
                    'altitude'   => rand(40,100),
                    'speed'      => rand(25,50),
                    'is_reached' => $i < 2,
                ]);
            }
        }

        $this->command->info('✅ Database seeded!');
        $this->command->table(['Role','Email','Password'],[
            ['Admin',  'admin@agritrek.com',           'password'],
            ['Expert', 'ankushnagokay4631@gmail.com',  'AnkushJatt23@aR'],
            ['Farmer', 'farmer@agritrek.com',          'password'],
        ]);
    }
}
