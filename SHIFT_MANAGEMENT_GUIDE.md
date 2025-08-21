# Shift Management - Case-Based Implementation

## Overview
Sistem ini mendukung manajemen shift yang fleksibel dengan konfigurasi berbeda per lokasi, memenuhi berbagai skenario bisnis.

## Case-Based Examples

### Case 1: Pos Utama (24/7 Operation)
```json
{
  "name": "Pos Utama - 24/7 Pagi",
  "start_time": "06:00",
  "end_time": "14:00", 
  "location_id": 1,
  "active_days": [1,2,3,4,5,6,7], // All week
  "capacity": 3, // 3 satpam per shift
  "status": "active",
  "description": "Shift pagi khusus Pos Utama (24/7 operation)"
}
```

### Case 2: Pos Selatan (Extended Hours, Weekdays Only)
```json
{
  "name": "Pos Selatan - Extended",
  "start_time": "07:00",
  "end_time": "19:00", // 12 hour shift
  "location_id": 2,
  "active_days": [1,2,3,4,5], // Mon-Fri only
  "capacity": 2,
  "status": "active",
  "description": "Shift panjang 12 jam untuk Pos Selatan (hari kerja)"
}
```

### Case 3: Pos Barat (Weekend Only)
```json
{
  "name": "Pos Barat - Weekend Only",
  "start_time": "08:00",
  "end_time": "20:00",
  "location_id": 3,
  "active_days": [6,7], // Sat-Sun only
  "capacity": 1,
  "status": "active",
  "description": "Shift khusus weekend untuk Pos Barat"
}
```

### Case 4: Global Shift (All Locations)
```json
{
  "name": "Shift Pagi",
  "start_time": "06:00",
  "end_time": "14:00",
  "location_id": null, // Available for all locations
  "active_days": [1,2,3,4,5], // Mon-Fri
  "capacity": 2,
  "status": "active",
  "description": "Shift pagi standar untuk hari kerja"
}
```

## Database Schema

### shifts table
- `id` - Primary key
- `name` - Shift name
- `start_time` - Shift start time
- `end_time` - Shift end time  
- `location_id` - Foreign key to locations (nullable for global shifts)
- `active_days` - JSON array [1-7] for days of week (1=Monday)
- `capacity` - Number of satpam allowed per shift
- `status` - 'active' or 'inactive'
- `description` - Optional description

## API Endpoints

### Get Shifts with Filters
```http
GET /api/shifts?location_id=1&day_of_week=1
```

### Create Location-Specific Shift
```http
POST /api/shifts
{
  "name": "Custom Shift",
  "start_time": "06:00",
  "end_time": "14:00",
  "location_id": 1,
  "active_days": [1,2,3,4,5],
  "capacity": 2,
  "status": "active",
  "description": "Custom description"
}
```

### Update Shift
```http
PUT /api/shifts/{id}
{
  "active_days": [6,7], // Change to weekend only
  "capacity": 1
}
```

## Frontend Features

### Admin Interface
- `/admin/shifts` - Full CRUD management
- Filter by location and day
- Visual day selection with checkboxes
- Capacity and status management

### Business Logic
- Shifts can be global (location_id = null) or location-specific
- Active days filter (empty array = all days)
- Capacity limits per shift
- Status management (active/inactive)

## Query Examples

### Get shifts for specific location on Monday
```php
Shift::forLocation(1)->forDay(1)->active()->get()
```

### Get available shifts for location and day
```php
Shift::where(function($q) use ($locationId) {
    $q->where('location_id', $locationId)->orWhereNull('location_id');
})->where(function($q) use ($dayOfWeek) {
    $q->whereNull('active_days')->orWhereJsonContains('active_days', $dayOfWeek);
})->active()->get()
```

## Benefits
1. **Flexible Scheduling** - Different shifts per location
2. **Day-Specific Operations** - Weekend vs weekday shifts  
3. **Capacity Management** - Control staffing levels
4. **Global vs Local** - Reusable shifts across locations
5. **Status Control** - Enable/disable shifts as needed
