# QR Code Attendance System

Sistem presensi berbasis QR Code dengan verifikasi wajah untuk monitoring kehadiran satpam di berbagai lokasi kerja. Aplikasi ini dilengkapi dengan dashboard admin untuk manajemen data, analisis kehadiran, dan prediksi AI untuk deteksi dini risiko keterlambatan.

## 🚀 Teknologi yang Digunakan

### Backend
- **Laravel 11** - PHP Framework
- **PostgreSQL** - Database
- **Laravel Sanctum** - Authentication
- **Eloquent ORM** - Database ORM
- **Service & Repository Pattern** - Architecture Pattern
- **PHPUnit** - Testing Framework
- **Face Data Storage** - Extended attendance model untuk face landmarks dan descriptors

### Frontend
- **Vue.js 3** - Frontend Framework (Composition API)
- **Tailwind CSS** - CSS Framework
- **Vue Router** - Routing
- **Pinia** - State Management
- **Axios** - HTTP Client
- **Heroicons** - Icons
- **Chart.js + vue-chartjs** - Data Visualization
- **jsQR** - Real-time QR Code Detection
- **face-api.js** - Face Detection & Recognition
- **Vite** - Build Tool

## 📋 Fitur Utama

### Admin Dashboard
- **Statistik Real-time**: Total satpam, lokasi, dan kehadiran harian
- **Visualisasi Data**: Chart tren kehadiran, distribusi per lokasi
- **Prediksi AI**: Deteksi risiko keterlambatan berdasarkan pola historis
- **Kelola Satpam**: CRUD data satpam dengan search dan filter
- **Kelola Lokasi**: Manajemen lokasi kerja dengan geolocation
- **Kelola QR Code**: Generate dan kelola QR code dengan expiry per jam
- **Laporan Presensi**: Export dan analisis data kehadiran
- **Data Presensi**: Kelola data kehadiran dengan bulk actions

### Satpam Attendance (New!)
- **Real-time QR Scanning**: Menggunakan kamera device untuk scan QR code secara langsung
- **Face Verification**: Verifikasi wajah menggunakan face-api.js untuk keamanan ekstra
- **Dual Camera Support**: 
  - Kamera belakang untuk scan QR code
  - Kamera depan untuk face verification
- **Live Camera Preview**: UI guide untuk positioning wajah yang optimal
- **Timezone Support**: WIB timezone untuk akurasi waktu presensi
- **Manual QR Input**: Fallback untuk input manual QR code
- **Location Validation**: Validasi lokasi GPS saat presensi
- **Audit Trail**: Semua aktivitas presensi tersimpan dengan face data

### Security Features
- **Face Landmark Detection**: Menggunakan tiny face detector dengan 68 landmark points
- **Face Descriptor Storage**: Face encoding untuk verifikasi identitas
- **Image Quality Validation**: Otomatis validasi kualitas foto wajah
- **Face Comparison**: Algoritma perbandingan wajah untuk anti-spoofing
- **Secure Image Capture**: Base64 encoding untuk transfer image yang aman

### Mobile-Responsive
- Optimized untuk penggunaan mobile dan desktop
- Progressive Web App (PWA) ready
- Touch-friendly interface dengan camera controls

## 🔧 Instalasi & Setup

### Prerequisites
- PHP >= 8.1
- Composer
- Node.js >= 16
- PostgreSQL >= 12
- Git

### Backend Setup

1. **Clone Repository**
```bash
git clone <repository-url>
cd qr-code-attendance-system
```

2. **Install Dependencies**
```bash
cd backend
composer install
```

3. **Environment Configuration**
```bash
cp .env.example .env
```

Edit file `.env` dengan konfigurasi database Anda:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=qr_attendance_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

4. **Generate Application Key**
```bash
php artisan key:generate
```

5. **Database Setup**
```bash
# Buat database PostgreSQL
createdb qr_attendance_db

# Run migrations
php artisan migrate

# Run seeders (optional)
php artisan db:seed
```

6. **Storage Setup**
```bash
php artisan storage:link
```

7. **Face Detection Models Setup**
```bash
# Create public/models directory
mkdir -p public/models

# Download required face-api.js models (Windows PowerShell)
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/refs/heads/master/weights/tiny_face_detector_model-weights_manifest.json" -OutFile "public/models/tiny_face_detector_model-weights_manifest.json"
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/refs/heads/master/weights/tiny_face_detector_model-shard1" -OutFile "public/models/tiny_face_detector_model-shard1"
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/refs/heads/master/weights/face_landmark_68_model-weights_manifest.json" -OutFile "public/models/face_landmark_68_model-weights_manifest.json"
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/refs/heads/master/weights/face_landmark_68_model-shard1" -OutFile "public/models/face_landmark_68_model-shard1"
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/refs/heads/master/weights/face_recognition_model-weights_manifest.json" -OutFile "public/models/face_recognition_model-weights_manifest.json"
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/refs/heads/master/weights/face_recognition_model-shard1" -OutFile "public/models/face_recognition_model-shard1"
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/refs/heads/master/weights/face_recognition_model-shard2" -OutFile "public/models/face_recognition_model-shard2"
```

8. **Start Backend Server**
```bash
php artisan serve
```
Backend akan berjalan di `http://localhost:8000`

### Frontend Setup

1. **Install Dependencies**
```bash
cd frontend
npm install

# Install additional dependencies for QR and face detection
npm install jsqr face-api.js
```

2. **Environment Configuration**
```bash
cp .env.example .env
```

Edit file `.env`:
```env
VITE_API_BASE_URL=http://localhost:8000/api
```

3. **Start Development Server**
```bash
npm run dev
```
Frontend akan berjalan di `http://localhost:5173`

### Production Build Requirements

**Important**: Untuk production, pastikan:
1. Face detection models sudah didownload ke `/public/models`
2. Camera permissions tersedia (HTTPS required untuk production)
3. Models dapat diakses via HTTP (bukan file://)

## 🔒 Camera & Security Permissions

### Development Setup
```bash
# Untuk testing di localhost, browser akan meminta permission camera
# Pastikan website diakses via HTTPS untuk production
```

### Required Browser Permissions
- **Camera Access**: Untuk QR scanning dan face capture
- **Microphone**: Tidak diperlukan (video tanpa audio)
- **Location**: Optional (untuk geolocation validation)

### Face Detection Models
Models yang diperlukan di `/public/models`:
- `tiny_face_detector_model-*` - Face detection
- `face_landmark_68_model-*` - Landmark detection  
- `face_recognition_model-*` - Face recognition

Total ukuran models: ~6MB

## 🔐 Sample Data & Kredensial Login

### Default Admin Account
```
Email: admin@example.com
Password: admin123
Role: admin
```

### Sample Satpam Accounts (untuk testing attendance)
```
Email: ahmad@example.com
Password: password
Role: security_guard

Email: budi@example.com  
Password: password
Role: security_guard
```

### Testing QR Codes
Sample QR codes yang bisa digunakan untuk testing:
```
c40ad10b-b677-4f03-91cb-cf868a8faf4b
a5f2e8d9-1234-5678-9abc-def012345678
b7c3f4e5-abcd-1234-efgh-567890abcdef
```

### Face Detection Testing
- Gunakan kamera depan dengan pencahayaan yang cukup
- Posisikan wajah di dalam oval guide yang disediakan
- Pastikan wajah menghadap langsung ke kamera

### Sample Data
Database seeder akan membuat:
- 3 Admin users
- 10 Security Guard users  
- 5 Locations (Pos Utara, Pos Selatan, dll) dengan GPS coordinates
- QR Codes dengan UUID format dan expiry per jam
- 100+ Attendance records dengan face data
- Sample shifts dan AI predictions

Jalankan seeder:
```bash
cd backend
php artisan db:seed
```

## 📱 Fitur Satpam Attendance

### QR Code Scanning Flow
1. **Start Camera**: Menggunakan kamera belakang untuk scan QR
2. **Real-time Detection**: jsQR melakukan deteksi real-time
3. **QR Validation**: Backend memvalidasi UUID dan expiry
4. **Face Capture**: Switch ke kamera depan untuk verifikasi wajah
5. **Face Analysis**: face-api.js menganalisis 68 landmark points
6. **Attendance Record**: Data tersimpan dengan timestamp WIB

### Face Verification Process
```javascript
// Face detection dengan tiny face detector
const detection = await faceapi
  .detectSingleFace(image, new faceapi.TinyFaceDetectorOptions({
    inputSize: 416,
    scoreThreshold: 0.3
  }))
  .withFaceLandmarks()
  .withFaceDescriptor()
```

### Camera Switching Logic
- **Environment Camera**: Untuk QR scanning (`facingMode: 'environment'`)
- **User Camera**: Untuk face capture (`facingMode: 'user'`)
- **Mirror Effect**: Front camera ditampilkan dengan `scaleX(-1)` untuk UX yang natural

### Data yang Disimpan
```json
{
  "qr_code_id": "uuid",
  "user_id": 1,
  "location_id": 1,
  "check_type": "check_in",
  "timestamp": "2025-08-21 08:00:00",
  "face_photo_url": "base64_image",
  "face_landmarks": [68_points_array],
  "face_descriptor": [128_dim_vector],
  "face_quality": "good"
}
```

## 📚 Dokumentasi API

### Authentication Endpoints

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

### Admin Dashboard Endpoints

#### Get Dashboard Stats
```http
GET /api/admin/dashboard/stats
Authorization: Bearer {token}
```

#### Get Attendance Chart Data
```http
GET /api/admin/dashboard/chart-data?period=7
Authorization: Bearer {token}
```

### User Management Endpoints

#### Get All Users (Satpam)
```http
GET /api/users?role=security_guard&page=1
Authorization: Bearer {token}
```

#### Create New User
```http
POST /api/users
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Nama Satpam",
  "email": "satpam@example.com",
  "password": "password",
  "role": "security_guard",
  "status": "active"
}
```

#### Update User
```http
PUT /api/users/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Name",
  "email": "updated@example.com",
  "status": "active"
}
```

#### Delete User
```http
DELETE /api/users/{id}
Authorization: Bearer {token}
```

### Location Management Endpoints

#### Get All Locations
```http
GET /api/locations
Authorization: Bearer {token}
```

#### Create Location
```http
POST /api/locations
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Pos Baru",
  "address": "Alamat Lengkap",
  "latitude": -6.2088,
  "longitude": 106.8456,
  "radius": 100
}
```

### QR Code Management Endpoints

#### Get QR Codes
```http
GET /api/qr-codes
Authorization: Bearer {token}
```

#### Generate QR Code (Updated)
```http
POST /api/qr-codes
Authorization: Bearer {token}
Content-Type: application/json

{
  "location_id": 1,
  "duration_hours": 24,
  "expires_at": "2025-08-22 08:00:00"
}
```

### Satpam Attendance Endpoints (New!)

#### Process QR Attendance with Face Data
```http
POST /api/satpam/qr-attendance
Authorization: Bearer {token}
Content-Type: application/json

{
  "qr_code": "c40ad10b-b677-4f03-91cb-cf868a8faf4b",
  "latitude": -6.2088,
  "longitude": 106.8456,
  "face_photo": "data:image/jpeg;base64,/9j/4AAQ...",
  "face_landmarks": [[x1,y1], [x2,y2], ...],
  "face_descriptor": [0.123, -0.456, ...],
  "face_quality": "good",
  "face_message": "Face detected successfully"
}
```

#### Get Today's Attendance
```http
GET /api/satpam/today-attendance
Authorization: Bearer {token}
```

#### Get Recent Attendance History
```http
GET /api/satpam/recent-attendance?limit=10
Authorization: Bearer {token}
```

### Attendance Endpoints

#### Get Attendance Data
```http
GET /api/attendance?page=1&user_id=1&location_id=1&date=2025-01-15
Authorization: Bearer {token}
```

#### Create Manual Attendance
```http
POST /api/attendance
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 1,
  "location_id": 1,
  "date": "2025-01-15",
  "check_in": "08:00:00",
  "check_out": "17:00:00",
  "status": "present"
}
```

### Reports Endpoints

#### Get Attendance Report
```http
GET /api/reports/attendance?period=month&location_id=1
Authorization: Bearer {token}
```

#### Export Report
```http
GET /api/reports/export?period=month&format=xlsx
Authorization: Bearer {token}
```

## 🧪 Testing

### Backend Testing

1. **Setup Testing Environment**
```bash
cd backend
cp .env.testing.example .env.testing
```

Edit `.env.testing` untuk menggunakan database testing terpisah:
```env
DB_DATABASE=qr_attendance_test_db
```

2. **Create Test Database**
```bash
createdb qr_attendance_test_db
```

3. **Run Tests**
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
php artisan test --coverage-html=coverage-report

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run with verbose output
php artisan test -v
```

### Frontend Testing

```bash
cd frontend

# Run unit tests
npm run test

# Run tests with coverage
npm run test:coverage

# Run e2e tests
npm run test:e2e

# Run tests in watch mode
npm run test:watch
```

## 📱 Fitur Frontend

### Admin Dashboard Features
1. **Real-time Statistics**: Monitoring kehadiran secara real-time
2. **Interactive Charts**: Line chart, donut chart, dan bar chart
3. **User Management**: 
   - CRUD operations untuk data satpam
   - Search dan filter
   - Bulk actions
4. **Location Management**: 
   - Card-based interface
   - Maps integration ready
   - GPS coordinates management
5. **QR Code Management**: 
   - Generate QR codes dengan durasi custom per jam
   - UUID-based QR codes untuk keamanan
   - Download QR code sebagai SVG
   - Track expiry dan usage statistics
6. **Reports & Analytics**: 
   - Comprehensive attendance reports
   - Export to Excel
   - Filter berdasarkan periode, lokasi, user
7. **Attendance Data Management**: 
   - Manual entry support
   - Edit existing records
   - Bulk operations
   - Face data visualization

### Satpam Attendance Interface (New!)
1. **Camera Controls**:
   - Real-time QR scanning dengan preview
   - Automatic camera switching (back/front)
   - Manual QR input sebagai fallback
   
2. **Face Verification UI**:
   - Live camera preview dengan mirror effect
   - Visual guides untuk positioning wajah
   - Real-time face detection feedback
   - Image quality validation
   
3. **Attendance Dashboard**:
   - Today's attendance status
   - Recent attendance history
   - Check-in/out timing dengan timezone WIB
   - Location information
   
4. **Error Handling & UX**:
   - Comprehensive error messages
   - Loading states dengan progress indicators
   - Success confirmations dengan auto-close
   - Retry mechanisms untuk network issues

### Technical Implementation
- **Vue 3 Composition API**: Reactive state management
- **Real-time Camera**: MediaDevices API dengan facingMode switching
- **Image Processing**: Canvas-based image capture dengan high quality
- **Face Detection**: client-side processing dengan face-api.js
- **Responsive Design**: Tailwind CSS dengan mobile-first approach

### Mobile Optimization
- Responsive design untuk semua layar
- Touch-friendly interface dengan gesture support
- Optimized camera controls untuk mobile devices
- Battery-efficient face detection
- Offline capability untuk basic functions

## 🔧 Troubleshooting

### Common Issues

#### Camera tidak berfungsi
```bash
# Pastikan menggunakan HTTPS untuk production
# Check browser permissions di Settings
# Verify camera access di browser console
```

#### Face detection model loading gagal
```bash
# Pastikan models sudah didownload ke public/models
ls -la public/models/
# Cek network requests di browser dev tools
# Verify model files dapat diakses via HTTP
```

#### QR Code tidak terdeteksi
```bash
# Pastikan QR code menggunakan UUID format
# Check lighting conditions
# Verify jsQR library loading
# Debug dengan console.log di captureQR function
```

#### Timezone tidak sesuai
```bash
# Backend menggunakan Asia/Jakarta (WIB)
# Frontend otomatis menggunakan local timezone
# Check config/app.php untuk timezone setting
```

### Development Tips

1. **Camera Testing**: Gunakan `chrome://settings/content/camera` untuk manage permissions
2. **Face Detection**: Test dengan pencahayaan yang baik dan jarak optimal (30-50cm)
3. **QR Generation**: Pastikan menggunakan UUID format untuk compatibility
4. **Database**: Gunakan separate database untuk testing dengan face data
5. **Models**: Download models sekali saja, size total ~6MB

## 🚀 Deployment

### Production Build

#### Backend
```bash
cd backend

# Install dependencies
composer install --no-dev --optimize-autoloader

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Ensure face detection models are accessible
chmod -R 755 public/models
```

#### Frontend
```bash
cd frontend

# Build for production
npm run build

# Preview production build
npm run preview

# Deploy ke server dengan HTTPS (required untuk camera access)
```

### Server Requirements for Production

#### Web Server Configuration
```nginx
# Nginx configuration untuk camera access
server {
    listen 443 ssl;
    server_name your-domain.com;
    
    # SSL certificates (required untuk camera access)
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # Static files untuk face detection models
    location /models/ {
        root /path/to/public;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

#### Environment Variables untuk Production
```env
# Laravel environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database production
DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_DATABASE=qr_attendance_production

# Face detection
FACE_MODELS_PATH=/public/models
FACE_DETECTION_ENABLED=true
```

### Docker Support (Optional)

```bash
# Build and run with Docker Compose
docker-compose up -d

# Run migrations in container
docker-compose exec backend php artisan migrate
```

## 🔧 Maintenance

### Database Maintenance
```bash
# Backup database
pg_dump qr_attendance_db > backup.sql

# Optimize database
php artisan model:prune --pretend
php artisan queue:prune-batches
```

### Log Management
```bash
# View logs
tail -f storage/logs/laravel.log

# Clear logs
php artisan log:clear
```

## 🤝 Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Create Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

Jika mengalami masalah atau membutuhkan bantuan:

1. **Check Documentation**: Baca dokumentasi ini terlebih dahulu
2. **Check Issues**: Lihat existing issues di repository
3. **Create Issue**: Buat issue baru dengan detail lengkap
4. **Contact**: Email ke support@example.com

## 🔄 Changelog

### Version 1.0.0 (Current)
- ✅ Authentication system dengan role-based access
- ✅ Admin dashboard dengan real-time statistics
- ✅ User management (CRUD satpam)
- ✅ Location management dengan GPS coordinates
- ✅ QR Code generation dengan UUID dan hourly expiry
- ✅ Real-time QR scanning dengan jsQR
- ✅ Face verification dengan face-api.js
- ✅ Dual camera support (back/front switching)
- ✅ Face landmark detection (68 points)
- ✅ Face descriptor storage untuk security
- ✅ Attendance tracking dengan face data
- ✅ WIB timezone support
- ✅ Mobile-responsive design dengan camera controls
- ✅ Data visualization dengan Chart.js
- ✅ AI predictions untuk keterlambatan
- ✅ Export reports to Excel
- ✅ Comprehensive testing suite

### Recent Updates (August 2025)
- 🆕 **Real QR Detection**: Implementasi jsQR untuk deteksi QR real-time
- 🆕 **Face Verification System**: Complete face detection dan recognition
- 🆕 **Camera Management**: Smart switching antara back/front camera
- 🆕 **Enhanced Security**: Face data storage untuk audit trail
- 🆕 **Improved UX**: Visual guides dan real-time feedback
- 🆕 **Performance Optimization**: Client-side face processing
- 🆕 **Error Handling**: Comprehensive error management dan recovery

### Technical Improvements
- 🔧 **Face Detection Models**: Tiny face detector dengan 68 landmarks
- � **Image Processing**: High-quality image capture dengan canvas
- 🔧 **State Management**: Vue 3 Composition API dengan reactive state
- � **Network Optimization**: Optimized API calls dan caching
- 🔧 **Browser Compatibility**: Modern browser APIs dengan fallbacks

### Upcoming Features
- 🔄 **Advanced Face Recognition**: Multiple face comparison algorithms
- 🔄 **Geofencing Validation**: Enhanced location verification
- 🔄 **Offline Capability**: PWA dengan offline attendance storage
- 🔄 **Real-time Notifications**: WebSocket untuk live updates
- 🔄 **Mobile app**: React Native implementation
- 🔄 **Advanced AI Analytics**: Pattern recognition dan behavior analysis
- 🔄 **Multi-tenant Support**: Multiple organization support
- 🔄 **Shift Scheduling**: Advanced scheduling system

---

**Made with ❤️ for Hackathon Sevima 2025**
