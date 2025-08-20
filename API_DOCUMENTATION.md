# QR Attendance System - API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
All endpoints except login require Bearer token authentication.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## Response Format

### Success Response
```json
{
  "success": true,
  "data": {},
  "message": "Success message"
}
```

### Error Response
```json
{
  "success": false,
  "error": "Error message",
  "errors": {}
}
```

## Authentication Endpoints

### POST /login
Login user and get access token.

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "admin123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "role": "admin"
    }
  }
}
```

### POST /logout
Logout current user.

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### GET /user
Get current authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

## Dashboard Endpoints

### GET /dashboard/stats
Get dashboard statistics.

**Query Parameters:**
- `period` (optional): today, week, month, year

**Response:**
```json
{
  "success": true,
  "data": {
    "total_satpam": 25,
    "total_locations": 8,
    "total_qr_codes": 15,
    "today": {
      "total_attendance": 23,
      "on_time_count": 20,
      "late_count": 3,
      "attendance_rate": 92
    }
  }
}
```

### GET /dashboard/chart-data
Get chart data for dashboard.

**Query Parameters:**
- `period` (required): 7, 14, 30, 90 (days)

**Response:**
```json
{
  "success": true,
  "data": {
    "labels": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    "datasets": [{
      "label": "Attendance",
      "data": [23, 25, 22, 24, 26, 15, 12],
      "borderColor": "#10B981"
    }]
  }
}
```

### GET /dashboard/activities
Get recent activities.

**Query Parameters:**
- `limit` (optional): number of activities (default: 10)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user": "Ahmad Wijaya",
      "action": "checked in at Pos Utara",
      "time": "08:05 AM",
      "type": "check_in",
      "icon": "CheckCircleIcon"
    }
  ]
}
```

### GET /dashboard/late-employees
Get employees with most late attendance.

**Query Parameters:**
- `period` (optional): 7, 14, 30 (days, default: 7)
- `limit` (optional): number of results (default: 5)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "name": "Pak Slamet",
      "location": "Pos Barat",
      "late_count": 5,
      "avg_late_minutes": 15
    }
  ]
}
```

## User Management Endpoints

### GET /users
Get list of users.

**Query Parameters:**
- `page` (optional): page number (default: 1)
- `per_page` (optional): items per page (default: 10)
- `role` (optional): admin, security_guard
- `status` (optional): active, inactive
- `search` (optional): search by name or email

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Ahmad Wijaya",
      "email": "ahmad@example.com",
      "role": "security_guard",
      "status": "active",
      "created_at": "2025-01-15T00:00:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 10,
    "total": 25,
    "from": 1,
    "to": 10
  }
}
```

### POST /users
Create new user.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role": "security_guard",
  "status": "active"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 26,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "security_guard",
    "status": "active"
  },
  "message": "User created successfully"
}
```

### GET /users/{id}
Get user by ID.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Ahmad Wijaya",
    "email": "ahmad@example.com",
    "role": "security_guard",
    "status": "active",
    "created_at": "2025-01-15T00:00:00Z",
    "attendance_stats": {
      "total_attendance": 22,
      "on_time_count": 18,
      "late_count": 4
    }
  }
}
```

### PUT /users/{id}
Update user.

**Request Body:**
```json
{
  "name": "Ahmad Wijaya Updated",
  "email": "ahmad.updated@example.com",
  "status": "active"
}
```

### DELETE /users/{id}
Delete user.

**Response:**
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

## Location Management Endpoints

### GET /locations
Get list of locations.

**Query Parameters:**
- `search` (optional): search by name or address
- `status` (optional): active, inactive

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Pos Utara",
      "address": "Jl. Utara No. 123",
      "latitude": -6.2088,
      "longitude": 106.8456,
      "radius": 100,
      "status": "active",
      "qr_codes_count": 3
    }
  ]
}
```

### POST /locations
Create new location.

**Request Body:**
```json
{
  "name": "Pos Timur",
  "address": "Jl. Timur No. 456",
  "latitude": -6.2000,
  "longitude": 106.8500,
  "radius": 150,
  "status": "active"
}
```

### PUT /locations/{id}
Update location.

### DELETE /locations/{id}
Delete location.

## QR Code Management Endpoints

### GET /qr-codes
Get list of QR codes.

**Query Parameters:**
- `location_id` (optional): filter by location
- `status` (optional): active, expired
- `search` (optional): search by location name

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "qr_12345abcdef67890",
      "location_id": 1,
      "location": {
        "id": 1,
        "name": "Pos Utara"
      },
      "expires_at": "2025-02-15T00:00:00Z",
      "is_expired": false,
      "scan_count": 45,
      "qr_image": "data:image/png;base64,..."
    }
  ]
}
```

### POST /qr-codes
Generate new QR code.

**Request Body:**
```json
{
  "location_id": 1,
  "duration_days": 30
}
```

### POST /qr-codes/{id}/renew
Renew expired QR code.

**Request Body:**
```json
{
  "duration_days": 30
}
```

### DELETE /qr-codes/{id}
Delete QR code.

## Attendance Endpoints

### GET /attendance
Get attendance data.

**Query Parameters:**
- `page` (optional): page number
- `per_page` (optional): items per page
- `user_id` (optional): filter by user
- `location_id` (optional): filter by location
- `date` (optional): filter by date (YYYY-MM-DD)
- `status` (optional): present, absent

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user": {
        "id": 1,
        "name": "Ahmad Wijaya",
        "email": "ahmad@example.com"
      },
      "location": {
        "id": 1,
        "name": "Pos Utara"
      },
      "date": "2025-01-15",
      "check_in": "08:05:00",
      "check_out": "17:00:00",
      "status": "present",
      "is_late": true,
      "late_minutes": 5,
      "notes": null
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 10,
    "total": 100
  }
}
```

### POST /attendance
Create manual attendance entry.

**Request Body:**
```json
{
  "user_id": 1,
  "location_id": 1,
  "date": "2025-01-15",
  "check_in": "08:00:00",
  "check_out": "17:00:00",
  "status": "present",
  "notes": "Manual entry"
}
```

### PUT /attendance/{id}
Update attendance record.

### DELETE /attendance/{id}
Delete attendance record.

### DELETE /attendance/bulk
Bulk delete attendance records.

**Request Body:**
```json
{
  "ids": [1, 2, 3, 4, 5]
}
```

## Reports Endpoints

### GET /reports/attendance
Get attendance report.

**Query Parameters:**
- `period` (optional): today, week, month, quarter, custom
- `start_date` (optional): start date for custom period
- `end_date` (optional): end date for custom period
- `location_id` (optional): filter by location
- `user_id` (optional): filter by user

**Response:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_attendance": 250,
      "on_time_attendance": 220,
      "late_attendance": 30,
      "absent_count": 15
    },
    "report_data": [
      {
        "user_id": 1,
        "user": {
          "id": 1,
          "name": "Ahmad Wijaya",
          "email": "ahmad@example.com"
        },
        "total_attendance": 22,
        "on_time_count": 18,
        "late_count": 4,
        "absent_count": 3,
        "attendance_percentage": 88,
        "avg_late_minutes": 15
      }
    ],
    "charts": {
      "attendance_trend": {},
      "location_distribution": {}
    }
  }
}
```

### GET /reports/user-attendance
Get user attendance history.

**Query Parameters:**
- `user_id` (required): user ID
- `period` (optional): period filter
- `start_date` (optional): start date
- `end_date` (optional): end date

### GET /reports/export
Export attendance report.

**Query Parameters:**
- Same as /reports/attendance
- `format` (optional): xlsx, pdf, csv (default: xlsx)

**Response:**
Binary file download

## Health Check Endpoint

### GET /health
Check API health status.

**Response:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "database": "connected",
    "cache": "working",
    "timestamp": "2025-01-15T10:30:00Z"
  }
}
```

## Error Codes

- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Rate Limiting
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated users

## Pagination
All list endpoints support pagination with the following parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 10, max: 100)

## Filtering and Searching
Most list endpoints support:
- `search`: Search in relevant fields
- `sort`: Sort field
- `sort_direction`: asc or desc
- Various filter parameters specific to each endpoint
