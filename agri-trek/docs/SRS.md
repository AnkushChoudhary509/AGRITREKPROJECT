# Software Requirements Specification (SRS)
## Agri-Trek: Precision Clustering of Aerial Objects Using Trajectory Analysis

**Version:** 1.0  
**Date:** 2024  
**Project Type:** Final Year Engineering Project  

---

## 1. Introduction

### 1.1 Purpose
This document describes the software requirements for **Agri-Trek**, a web-based smart agriculture and drone monitoring system. It defines the functional and non-functional requirements for all modules.

### 1.2 Scope
Agri-Trek will provide:
- Digitized farmer and land record management
- Government scheme administration with application workflow
- Simulated drone fleet monitoring with real-time map visualization
- Waypoint-based drone navigation simulation
- K-Means trajectory clustering for identifying drone hotspots
- Basic computer vision simulation for crop analysis
- Multi-sensor fusion demonstration

### 1.3 Definitions
- **Drone Log / Telemetry:** GPS + speed + altitude data recorded at intervals
- **Waypoint:** A predefined GPS coordinate a drone must visit in sequence
- **Cluster:** A group of closely-spaced trajectory points identified by K-Means
- **Sensor Fusion:** Combining readings from multiple sensors to improve accuracy

---

## 2. Overall Description

### 2.1 Product Perspective
Agri-Trek is a standalone web application. It does not depend on any external paid APIs. Maps use free OpenStreetMap tiles via Leaflet.js. The drone monitoring component accepts data via a REST API that can be connected to real drone hardware or simulation scripts.

### 2.2 User Classes

| User | Permissions |
|---|---|
| Admin | Full access to all modules |
| Farmer | View own profile, land records, apply for schemes |

### 2.3 Operating Environment
- Server: PHP 8.2+, Apache 2.4+, MySQL 8.0+
- Client: Modern web browser (Chrome, Firefox, Edge)
- Platform: XAMPP / WAMP / Laragon (local) or Linux VPS (production)

---

## 3. Functional Requirements

### 3.1 Authentication Module
- **FR-AUTH-01:** System shall provide a login page with email and password.
- **FR-AUTH-02:** System shall redirect authenticated users to the dashboard.
- **FR-AUTH-03:** System shall enforce role-based access (admin vs farmer).
- **FR-AUTH-04:** Admin accounts shall have full access; farmer accounts shall see only their data.
- **FR-AUTH-05:** System shall support secure logout with session invalidation.

### 3.2 Farmer Management Module
- **FR-FARM-01:** Admin shall be able to create, read, update, and delete farmer records.
- **FR-FARM-02:** System shall store: Name, Mobile, Address, Village, District, Aadhaar, DOB, Bank details.
- **FR-FARM-03:** System shall mask Aadhaar numbers in display (first 4 and last 4 digits only).
- **FR-FARM-04:** System shall provide a search feature filtering by name, village, mobile.
- **FR-FARM-05:** System shall support soft delete (records recoverable).
- **FR-FARM-06:** Each farmer shall have a profile page showing land holdings and scheme status.

### 3.3 Land Management Module
- **FR-LAND-01:** System shall store land records with area, soil type, crop type, irrigation type.
- **FR-LAND-02:** System shall capture GPS coordinates for each land parcel.
- **FR-LAND-03:** System shall display land locations on an interactive Leaflet.js map.
- **FR-LAND-04:** Admin shall be able to perform full CRUD on land records.
- **FR-LAND-05:** Land map shall use color-coded markers by crop type.

### 3.4 Beneficiary Scheme Module
- **FR-SCH-01:** Admin shall be able to create and manage government schemes.
- **FR-SCH-02:** Each scheme shall have name, description, eligibility, subsidy amount, dates.
- **FR-SCH-03:** Farmers shall be able to apply for any active scheme.
- **FR-SCH-04:** Each farmer can apply for a given scheme only once.
- **FR-SCH-05:** Admin shall be able to approve or reject applications.
- **FR-SCH-06:** Application status shall be: pending / approved / rejected.

### 3.5 Drone Monitoring Module
- **FR-DRONE-01:** System shall maintain a drone fleet registry (name, hardware ID, model, status).
- **FR-DRONE-02:** System shall display drone locations on a live map with status colors.
- **FR-DRONE-03:** System shall store telemetry: latitude, longitude, speed, altitude, direction, timestamp.
- **FR-DRONE-04:** System shall provide a REST API to accept telemetry data from hardware.
- **FR-DRONE-05:** Each drone shall show a path trail of its last 20 positions.

### 3.6 Waypoint Navigation Module
- **FR-WP-01:** Admin shall be able to create waypoints with GPS coordinates, altitude, speed, and route name.
- **FR-WP-02:** Waypoints shall be grouped into named routes.
- **FR-WP-03:** System shall animate a drone marker traversing a selected route on the map.
- **FR-WP-04:** System shall visually distinguish reached vs pending waypoints.
- **FR-WP-05:** Simulation shall update waypoint status in the database as drone passes each point.

### 3.7 Precision Clustering Module
- **FR-CLUS-01:** System shall cluster drone telemetry points using the K-Means algorithm.
- **FR-CLUS-02:** User shall be able to select K value (2–10).
- **FR-CLUS-03:** Each cluster shall be rendered with a distinct color on the map.
- **FR-CLUS-04:** System shall display centroid location, average speed, average altitude per cluster.
- **FR-CLUS-05:** Cluster radius circles shall be drawn on the map.
- **FR-CLUS-06:** System shall label clusters by zone type (Monitoring Zone, Hotspot, etc.).

### 3.8 Computer Vision Module (Simulation)
- **FR-VIS-01:** System shall allow upload of field/drone images (JPG, PNG, WEBP).
- **FR-VIS-02:** System shall support 5 detection modes: crop health, object detection, field segmentation, weed detection, water stress.
- **FR-VIS-03:** System shall overlay detection bounding boxes on the uploaded image.
- **FR-VIS-04:** Detection results shall include label, confidence score, and area percentage.
- **FR-VIS-05:** System shall provide a crop health recommendation based on detection results.
- **FR-VIS-06:** Analysis history shall be stored and displayed.

### 3.9 Sensor Fusion Module
- **FR-SEN-01:** System shall display simulated readings from GPS, speed sensor, altimeter, and camera.
- **FR-SEN-02:** System shall apply weighted average fusion (GPS:40%, Speed:25%, Altimeter:20%, Camera:15%).
- **FR-SEN-03:** System shall display fused position estimate with confidence percentage.
- **FR-SEN-04:** System shall show a line chart comparing GPS-only error vs fused output error.
- **FR-SEN-05:** System shall display error reduction percentage achieved by fusion.

---

## 4. Non-Functional Requirements

- **NFR-01 Performance:** Pages shall load in under 3 seconds on local server.
- **NFR-02 Security:** Passwords shall be hashed using bcrypt. CSRF protection on all forms.
- **NFR-03 Usability:** UI shall be responsive across desktop and tablet browsers.
- **NFR-04 Reliability:** Application shall handle invalid inputs gracefully with user-friendly messages.
- **NFR-05 Maintainability:** Code shall follow Laravel MVC conventions with comments on key sections.
- **NFR-06 Portability:** Application shall run on XAMPP, WAMP, and Laragon without modification.

---

## 5. System Constraints

- No real-time WebSocket communication (page refresh or periodic polling only)
- Computer vision uses simulation logic, not real deep learning models
- Maps use free OpenStreetMap tiles (no Google Maps API key required)
- Drone simulation is visual-only; no actual drone hardware required

---

*End of SRS Document*
