# Agri-Trek

**Precision Clustering of Aerial Objects Using Trajectory Analysis**

A final-year engineering project that combines agricultural field management with simulated drone monitoring. Built in PHP Laravel, it lets you manage farmer records, track land parcels on a map, apply for government schemes, and visualise how agricultural drones would move across fields — including trajectory clustering, waypoint navigation, a basic computer vision module, and sensor fusion.

---

## What this project actually does

The idea came from a real problem: most farmers in India still manage records on paper, and drone-based crop monitoring is becoming more common but the data rarely gets organised in one place.

This project tries to bridge that gap with a simple web dashboard. On one side you have the administrative stuff — farmer profiles, land records with GPS coordinates, scheme applications and approvals. On the other side you have the drone simulation — drones fly predefined waypoint routes, their telemetry (latitude, longitude, speed, altitude) gets logged, and then a K-Means clustering algorithm groups those trajectory points to identify which areas of a field got the most monitoring coverage.

The computer vision module lets you upload an actual field photo. It reads the real pixel data from the image (green/brown/yellow distribution) to estimate crop health, weed presence, water stress, and nitrogen deficiency — not random values, actual color analysis. If the PHP GD library is enabled on your server, it does real pixel sampling. If not, it falls back to a realistic simulation.

---

## Accounts

There are three types of accounts. You cannot change this from the UI.

| Who | Email | Password | What they can do |
|-----|-------|----------|-----------------|
| Admin | admin@agritrek.com | password | Everything |
| Expert | ankushnagokay4631@gmail.com | AnkushJatt23@aR | Everything (hardcoded in .env, cannot register) |
| Farmer | farmer@agritrek.com | password | View own records, apply for schemes |

Farmers can register new accounts through the signup page. Expert and Admin accounts cannot be created through signup — the expert credentials are fixed in the `.env` file.

---

## Modules

**Farmer Management** — Add, edit, delete farmer records. Stores name, mobile, address, village, district, Aadhaar number (masked in display), bank account, and IFSC. Soft deletes so nothing is permanently lost by accident.

**Land Records** — Each farmer can own multiple land parcels. You store the crop type, soil type, area in acres, irrigation method, and GPS coordinates. There is a map picker on the form so you can click on the map to set the coordinates instead of typing them.

**Beneficiary Schemes** — Admin adds government schemes with subsidy amounts, eligibility criteria, and validity dates. Farmers can apply. Admin can approve or reject with remarks. One farmer can only apply for the same scheme once.

**Drone Monitoring** — Drones are registered with a hardware ID. Their telemetry is logged via a REST API endpoint (or through the seeder for demo data). Each drone shows its current position on the map and you can see a trail of its recent path.

**Waypoint Navigation** — You define ordered GPS waypoints that form a route, assign the route to a drone, and then simulate the drone flying through those points on the map. Each waypoint animates step by step with a 2-second interval. The simulation marks waypoints as reached in the database as it goes.

**K-Means Clustering** — Takes all the drone log coordinates and groups them into K clusters (you pick K from 2 to 10). Each cluster gets a centroid, a coloured circle on the map, and stats like average speed and altitude. The idea is that heavily clustered areas were surveyed more by the drone.

**Computer Vision** — Upload a JPG/PNG field photo. The server reads actual pixel colour statistics using PHP GD, calculates an approximate NDVI vegetation index from the RGB ratios, and maps those colour distributions to detection classes (healthy crop, diseased crop, weeds, water stress, bare soil, waterlogging, nitrogen deficiency). Results are shown as bounding boxes on the image with confidence scores.

**Sensor Fusion** — Demonstrates the concept of combining GPS, speed sensor, altimeter, and camera data using a weighted average (GPS gets 40%, speed sensor 25%, altimeter 20%, camera 15%). Shows a chart comparing the position error of raw GPS vs the fused output.

---

## Setup

You need PHP 8.2+, MySQL, and Composer. XAMPP or Laragon works fine.

```bash
# 1. Extract the project folder, then go into it
cd agri-trek

# 2. Install dependencies
composer install

# 3. The .env file is included with APP_KEY already set
#    Just update the database credentials if yours are different
#    Default XAMPP config (root, no password) works as-is

# 4. Create the database
# Open phpMyAdmin → New → Name it "agritrek" → Create

# 5. Run migrations and seed demo data
php artisan migrate:fresh --seed

# 6. Start the server
php artisan serve
```

Open http://localhost:8000 in your browser.

---

## Email / Password Reset

Password reset emails are sent via Gmail SMTP. To make this work you need to:

1. Enable 2-Step Verification on your Gmail account
2. Go to https://myaccount.google.com/apppasswords
3. Generate an App Password (select Mail)
4. Open `.env` and set `MAIL_PASSWORD` to that 16-character password

If you skip this, the forgot-password page will still work — it just shows the new temporary password on screen instead of emailing it. Good enough for a local demo.

For the expert account, the "Forgot Password" button sends the fixed credentials to `ankushnagokay4631@gmail.com`.

---

## REST API

The drone hardware (or a simulator script) can push telemetry to the system.

```
POST /api/drones/{drone_id}/log
```

Body:
```json
{
  "latitude": 23.5120,
  "longitude": 72.4900,
  "speed": 45,
  "altitude": 80,
  "direction": 180
}
```

```
GET /api/drones
GET /api/drones/{drone_id}/path
```

The path endpoint returns GeoJSON so you can load it into any mapping tool.

---

## Tech used

- PHP 8.3 / Laravel 11
- MySQL 8
- Bootstrap 5 + Bootstrap Icons
- Leaflet.js 1.9.4 (maps, OpenStreetMap tiles)
- Chart.js 4.4 (bar charts, doughnut, radar, line)
- PHP GD (real image pixel analysis in computer vision module)
- K-Means implemented from scratch in PHP (no external ML library)

No Node.js, no npm, no Vite. Everything runs on a plain XAMPP stack.

---

## Project structure (short version)

```
app/Http/Controllers/   — 11 controllers
app/Models/             — 9 Eloquent models
database/migrations/    — 7 migration files
database/seeders/       — DatabaseSeeder with full demo data
resources/views/        — 28 Blade templates
routes/web.php          — 34 named routes
routes/api.php          — 3 API endpoints
docs/database_setup.sql — raw SQL if you prefer importing manually
tests/                  — feature and unit tests
```

---

## Notes for viva

A few things likely to come up:

**Why K-Means and not DBSCAN?** K-Means is simpler to explain and implement from scratch. DBSCAN would be better for arbitrary cluster shapes but requires tuning epsilon and min-points which is harder to demo live.

**Is the computer vision real?** It does real pixel analysis using PHP GD — colour histogram sampling, NDVI approximation from RGB. It is not a trained neural network. In production you would replace the PHP analysis with a YOLOv8 API call; the rest of the pipeline (bounding box display, confidence scores, recommendations) is already in the correct format for that.

**Why PHP for image analysis instead of Python?** To avoid a second server process. For a college project, keeping everything in one Laravel stack is easier to set up and explain. A production system would use a Python microservice with OpenCV and PyTorch.

**What is sensor fusion actually doing?** It is a weighted average: `fused = (GPS × 0.4) + (speed_dead_reckoning × 0.25) + (altimeter × 0.20) + (camera_estimate × 0.15)`. Real systems use Kalman filters but the weighted average demonstrates the same concept — multiple noisy sensors producing a more accurate combined estimate.

---

## Screens

Login → Dashboard → Farmers → Lands (with map) → Schemes → Drone Monitoring → Waypoints (simulation) → Clustering → Computer Vision → Sensor Fusion

---

*Made for academic submission. Not intended for production use.*
