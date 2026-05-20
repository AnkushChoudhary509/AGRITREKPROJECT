# 🚁 Agri-Trek: Precision Clustering of Aerial Objects Using Trajectory Analysis

> A smart agriculture management and aerial drone monitoring system built with **Laravel 11**, **MySQL**, **Bootstrap 5**, **Leaflet.js**, and **Chart.js**.

---

## 📌 Project Abstract

**Agri-Trek** is a full-stack web application that digitizes farmer management, land records, and government scheme administration while simulating agricultural drone operations. It demonstrates key concepts from drone technology including waypoint-based navigation, computer vision crop analysis, multi-sensor fusion, and K-Means trajectory clustering — all implemented as educational simulations suitable for a final-year engineering project.

---

## 🎯 Key Features

| Module | Description |
|---|---|
| 🔐 Authentication | Role-based login (Admin / Farmer) |
| 👨‍🌾 Farmer Management | Full CRUD with search, profiles, Aadhaar masking |
| 🗺️ Land Management | GPS-mapped land parcels with Leaflet map picker |
| 🏆 Beneficiary Schemes | Scheme CRUD + farmer applications + approval workflow |
| 🚁 Drone Monitoring | Real-time map tracking, telemetry dashboard |
| 📍 Waypoint Navigation | Route creation + animated drone simulation |
| 🧩 Precision Clustering | K-Means on trajectory data with color-coded map visualization |
| 👁️ Computer Vision | Image upload + simulated crop/weed/stress detection |
| 📡 Sensor Fusion | Weighted GPS+Camera+Speed+Altimeter fusion with error comparison |
| 📊 Dashboard | Live stats, charts, maps, recent activity |
| 🔌 REST API | Drone telemetry API (push/pull) for hardware integration |

---

## 🛠️ Technology Stack

- **Backend:** PHP 8.2, Laravel 11
- **Database:** MySQL 8.0 (SQLite for testing)
- **Frontend:** Bootstrap 5.3, Bootstrap Icons
- **Maps:** Leaflet.js 1.9.4 (OpenStreetMap tiles)
- **Charts:** Chart.js 4.4
- **Server:** Apache (XAMPP/WAMP/Laragon) or PHP built-in server

---

## ⚙️ Installation Guide

### Prerequisites

- PHP >= 8.2 with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`
- MySQL 8.0+ (via XAMPP/WAMP/Laragon or standalone)
- Composer 2.x
- Node.js (optional, only if compiling assets)

---

### Step 1 — Clone / Extract the Project

```bash
# If cloning from repository:
git clone https://github.com/your-repo/agri-trek.git
cd agri-trek

# Or extract the ZIP and navigate to the folder:
cd agri-trek
```

---

### Step 2 — Install PHP Dependencies

```bash
composer install
```

---

### Step 3 — Configure Environment

```bash
# Copy the example env file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Open `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agritrek
DB_USERNAME=root
DB_PASSWORD=
```

---

### Step 4 — Create Database

Open **phpMyAdmin** (or MySQL CLI) and create a database:

```sql
CREATE DATABASE agritrek CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

### Step 5 — Run Migrations and Seeders

```bash
# Run all migrations (creates all tables)
php artisan migrate

# Seed the database with demo data
php artisan db:seed
```

This creates:
- **Admin account:** `admin@agritrek.com` / `password`
- **Farmer account:** `farmer@agritrek.com` / `password`
- 10 farmers, 20+ land records, 6 schemes, 5 drones, 150+ drone logs, 15 waypoints

---

### Step 6 — Create Storage Symlink

```bash
php artisan storage:link
```

---

### Step 7 — Run the Application

```bash
# Using PHP built-in server
php artisan serve

# Or configure Apache virtual host pointing to /public
```

Open your browser: **http://localhost:8000**

---

### Step 8 — Run Tests (Optional)

```bash
php artisan test
# or
./vendor/bin/phpunit
```

---

## 🗃️ Database Schema Summary

| Table | Description |
|---|---|
| `users` | Login accounts (admin/farmer roles) |
| `farmers` | Farmer personal and financial details |
| `lands` | Agricultural land parcels with GPS |
| `schemes` | Government beneficiary schemes |
| `scheme_applications` | Farmer applications to schemes |
| `drones` | Registered drone fleet |
| `drone_logs` | Telemetry data (lat, lng, speed, altitude, direction) |
| `waypoints` | Predefined GPS navigation points per route |
| `vision_analyses` | Computer vision detection history |

---

## 🔌 REST API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/drones` | List all drones with latest telemetry |
| POST | `/api/drones/{id}/log` | Push telemetry from drone hardware |
| GET | `/api/drones/{id}/path` | Get route history as GeoJSON |

### Example: Push Telemetry

```bash
curl -X POST http://localhost:8000/api/drones/DRONE-001/log \
  -H "Content-Type: application/json" \
  -d '{"latitude":23.512,"longitude":72.49,"speed":45,"altitude":80,"direction":180}'
```

---

## 📐 ER Diagram Description

```
USERS ─── (farmer_id FK) ───> FARMERS
FARMERS ──────────────────┬──> LANDS (farmer_id FK)
                          └──> SCHEME_APPLICATIONS (farmer_id FK)
SCHEMES ──────────────────────> SCHEME_APPLICATIONS (scheme_id FK)
DRONES ───────────────────┬──> DRONE_LOGS (drone_id FK)
                          └──> WAYPOINTS (drone_id FK)
```

---

## 📊 DFD (Data Flow Description)

**Level 0 (Context Diagram):**
- External entities: Admin, Farmer, Drone Hardware
- System: Agri-Trek Web Application
- Data flows: Login credentials, Farmer data, Land data, Scheme applications, Drone telemetry

**Level 1 (Main Processes):**
1. Authentication Process → validates credentials → User session
2. Farmer Management → CRUD → Farmers table
3. Land Management → GPS capture + CRUD → Lands table
4. Scheme Management → Eligibility check + approval → Applications table
5. Drone Monitoring → API telemetry → DroneLog table → Map display
6. Clustering Process → DroneLog data → K-Means → Cluster visualization
7. Vision Analysis → Image upload → Simulated detection → VisionAnalysis table
8. Sensor Fusion → Multiple sensor inputs → Weighted fusion → Dashboard display

---

## 🧪 Test Cases Summary

| ID | Module | Test Case | Expected Result |
|---|---|---|---|
| TC-01 | Auth | Valid login | Redirected to dashboard |
| TC-02 | Auth | Invalid password | Error message shown |
| TC-03 | Farmer | Create farmer | Record in DB, success message |
| TC-04 | Farmer | Duplicate mobile | Validation error |
| TC-05 | Drone | Register drone | Drone added to fleet |
| TC-06 | API | POST telemetry | 200 OK, log created |
| TC-07 | API | Invalid lat/lng | 422 Validation error |
| TC-08 | Cluster | Run K-Means (k=4) | 4 clusters returned |
| TC-09 | Cluster | Insufficient data | Error message |
| TC-10 | Scheme | Apply for scheme | Application created |
| TC-11 | Scheme | Approve application | Status updated to approved |
| TC-12 | Vision | Upload + analyze | Detections displayed |
| TC-13 | Waypoint | Simulate route | Drone animates through WPs |
| TC-14 | Role | Farmer accesses admin | 403 Forbidden |

---

## 📁 Project Structure

```
agri-trek/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── FarmerController.php
│   │   │   ├── LandController.php
│   │   │   ├── SchemeController.php
│   │   │   ├── DroneController.php
│   │   │   ├── WaypointController.php
│   │   │   ├── ClusteringController.php
│   │   │   ├── VisionController.php
│   │   │   └── SensorFusionController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   ├── Models/
│   │   ├── User.php, Farmer.php, Land.php
│   │   ├── Scheme.php, SchemeApplication.php
│   │   ├── Drone.php, DroneLog.php
│   │   ├── Waypoint.php, VisionAnalysis.php
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   ├── migrations/       # 6 migration files
│   ├── seeders/          # DatabaseSeeder with full demo data
│   └── factories/        # User, Farmer, Drone, DroneLog factories
├── resources/views/
│   ├── layouts/app.blade.php     # Master layout with sidebar
│   ├── auth/login.blade.php
│   ├── admin/dashboard.blade.php
│   ├── farmers/   index, form, show
│   ├── lands/     index, form, show
│   ├── schemes/   index, form, show, applications
│   ├── drones/    index, form, show, logs
│   ├── waypoints/ index, form
│   ├── clustering/index
│   ├── vision/    index
│   └── sensors/   index
├── routes/
│   ├── web.php            # All web routes
│   └── api.php            # Drone telemetry API
├── tests/
│   ├── Feature/           # FarmerTest, DroneTest, ClusteringTest, AuthTest
│   └── Unit/              # AlgorithmTest (K-Means + Sensor Fusion)
├── public/                # Web root (index.php, .htaccess)
├── config/                # app.php, auth.php, database.php
├── .env.example
├── composer.json
├── phpunit.xml
└── README.md
```

---

## 👨‍💻 Demo Credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@agritrek.com | password |
| Farmer | farmer@agritrek.com | password |

---

## 📝 License

This project is developed for academic purposes under the MIT License.

---

*Built with ❤️ for Smart Agriculture — Final Year Project*
