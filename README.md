# QR Code Attendance System

Sistem presensi berbasis QR Code untuk monitoring kehadiran satpam di berbagai lokasi kerja. Aplikasi ini dilengkapi dengan dashboard admin untuk manajemen data, analisis kehadiran, dan prediksi AI untuk deteksi dini risiko keterlambatan.

## 🚀 Teknologi yang Digunakan

### Backend
- **Laravel 11** - PHP Framework
- **PostgreSQL** - Database
- **Laravel Sanctum** - Authentication
- **Eloquent ORM** - Database ORM
- **Service & Repository Pattern** - Architecture Pattern
- **PHPUnit** - Testing Framework

### Frontend
- **Vue.js 3** - Frontend Framework (Composition API)
- **Tailwind CSS** - CSS Framework
- **Vue Router** - Routing
- **Pinia** - State Management
- **Axios** - HTTP Client
- **Heroicons** - Icons
- **Chart.js + vue-chartjs** - Data Visualization
- **Vite** - Build Tool

## 📋 Fitur Utama

### Admin Dashboard
- **Statistik Real-time**: Total satpam, lokasi, dan kehadiran harian
- **Visualisasi Data**: Chart tren kehadiran, distribusi per lokasi
- **Prediksi AI**: Deteksi risiko keterlambatan berdasarkan pola historis
- **Kelola Satpam**: CRUD data satpam dengan search dan filter
- **Kelola Lokasi**: Manajemen lokasi kerja
- **Kelola QR Code**: Generate dan kelola QR code untuk setiap lokasi
- **Laporan Presensi**: Export dan analisis data kehadiran
- **Data Presensi**: Kelola data kehadiran dengan bulk actions

### Mobile-Responsive
- Optimized untuk penggunaan mobile dan desktop
- Progressive Web App (PWA) ready

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

7. **Start Backend Server**
```bash
php artisan serve
```
Backend akan berjalan di `http://localhost:8000`

### Frontend Setup

1. **Install Dependencies**
```bash
cd frontend
npm install
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

## 🔐 Sample Data & Kredensial Login

### Default Admin Account
```
Email: admin@example.com
Password: admin123
Role: admin
```

### Sample Satpam Accounts
```
Email: ahmad@example.com
Password: password
Role: security_guard

Email: budi@example.com
Password: password
Role: security_guard
```

### Sample Data
Database seeder akan membuat:
- 3 Admin users
- 10 Security Guard users  
- 5 Locations (Pos Utara, Pos Selatan, dll)
- 20 QR Codes
- 100+ Attendance records
- Sample shifts dan AI predictions

Jalankan seeder:
```bash
cd backend
php artisan db:seed
```

## 📚 Dokumentasi API

### Authentication Endpoints

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "admin123"
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

#### Generate QR Code
```http
POST /api/qr-codes
Authorization: Bearer {token}
Content-Type: application/json

{
  "location_id": 1,
  "duration_days": 30
}
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
5. **QR Code Management**: 
   - Generate QR codes dengan durasi custom
   - Download QR code sebagai image
   - Track scan statistics
6. **Reports & Analytics**: 
   - Comprehensive attendance reports
   - Export to Excel
   - Filter berdasarkan periode, lokasi, user
7. **Attendance Data Management**: 
   - Manual entry support
   - Edit existing records
   - Bulk operations

### Mobile Optimization
- Responsive design untuk semua layar
- Touch-friendly interface
- Optimized navigation untuk mobile

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
```

#### Frontend
```bash
cd frontend

# Build for production
npm run build

# Preview production build
npm run preview
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
- ✅ Location management
- ✅ QR Code generation dan management
- ✅ Attendance tracking dan reporting
- ✅ Data visualization dengan Chart.js
- ✅ Mobile-responsive design
- ✅ AI predictions untuk keterlambatan
- ✅ Export reports to Excel
- ✅ Comprehensive testing suite

### Upcoming Features
- 🔄 Real-time notifications
- 🔄 Mobile app (React Native)
- 🔄 Advanced AI analytics
- 🔄 Geofencing validation
- 🔄 Shift scheduling system
- 🔄 Multi-tenant support

---

**Made with ❤️ for Hackathon Sevima 2025**
