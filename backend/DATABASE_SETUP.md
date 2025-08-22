# Database Setup & CSV Data Import

## Overview
Sistem ini telah dikonfigurasi untuk menggunakan file CSV sebagai sumber data seeding, memungkinkan setup database yang mudah dan konsisten untuk tes fungsional.

## File CSV yang Tersedia

### 1. users.csv
**Lokasi**: `backend/database/csv/users.csv`
**Kolom**: name, email, username, password, role, photo, telegram_chat_id, telegram_username, telegram_notifications_enabled
**Data**: 8 pengguna (1 admin, 7 satpam)

### 2. locations.csv  
**Lokasi**: `backend/database/csv/locations.csv`
**Kolom**: name, latitude, longitude, address, status
**Data**: 7 lokasi kerja aktif

### 3. shifts.csv
**Lokasi**: `backend/database/csv/shifts.csv`
**Kolom**: name, start_time, end_time
**Data**: 5 shift kerja (pagi, siang, malam, normal, weekend)

### 4. qr_codes.csv
**Lokasi**: `backend/database/csv/qr_codes.csv`
**Kolom**: location_id, shift_id, code, expires_at, scan_count
**Data**: 15 QR codes untuk berbagai kombinasi lokasi dan shift

### 5. attendances.csv
**Lokasi**: `backend/database/csv/attendances.csv`
**Kolom**: user_id, location_id, shift_id, qr_code_id, scanned_at, check_out_time, status, late_category, photo_url, face_photo_url, latitude, longitude, check_out_latitude, check_out_longitude, distance, face_descriptor, face_quality_status, face_validation_message, notes
**Data**: 15 record presensi dengan berbagai status (tepat waktu, terlambat)

### 6. ai_predictions.csv
**Lokasi**: `backend/database/csv/ai_predictions.csv`
**Kolom**: user_id, location_id, predicted_for_date, risk_score, reason
**Data**: 10 prediksi AI untuk keterlambatan karyawan

### 7. notifications.csv
**Lokasi**: `backend/database/csv/notifications.csv`
**Kolom**: user_id, type, message, sent_at
**Data**: 6 notifikasi sistem (telegram/email) untuk berbagai event

## Seeder Classes

### DatabaseSeeder.php
Seeder utama yang memanggil semua seeder CSV dalam urutan yang benar:
1. UserCsvSeeder
2. LocationCsvSeeder  
3. ShiftCsvSeeder
4. QrCodeCsvSeeder
5. AttendanceCsvSeeder
6. AiPredictionCsvSeeder
7. NotificationCsvSeeder

### Individual CSV Seeders
- `UserCsvSeeder.php` - Import pengguna dengan role admin/satpam
- `LocationCsvSeeder.php` - Import lokasi kerja
- `ShiftCsvSeeder.php` - Import jadwal shift
- `QrCodeCsvSeeder.php` - Import QR codes dengan relasi ke lokasi dan shift
- `AttendanceCsvSeeder.php` - Import data presensi dengan validasi face recognition
- `AiPredictionCsvSeeder.php` - Import prediksi AI untuk keterlambatan
- `NotificationCsvSeeder.php` - Import notifikasi sistem

## Kompatibilitas Database

### Migrasi yang Telah Divalidasi
✅ Semua migrasi kompatibel dengan data CSV:
- Kolom enum values sesuai (status: 'aktif'/'nonaktif')
- Foreign key constraints terpenuhi
- Kolom nullable ditangani dengan benar
- Timestamp format sesuai dengan Laravel

### Struktur Tabel Final
- **users**: 12 kolom (termasuk telegram fields)
- **locations**: 8 kolom (termasuk status enum)
- **shifts**: 5 kolom (basic shift info)
- **qr_codes**: 6 kolom (termasuk scan_count)
- **attendances**: 22 kolom (termasuk face recognition dan checkout fields)
- **ai_predictions**: 6 kolom (prediksi dengan risk score)
- **notifications**: 5 kolom (sistem notifikasi telegram/email)

## Cara Penggunaan

### Setup Awal Database
```bash
php artisan migrate:fresh --seed
```

### Re-seed Data Tanpa Drop Tables
```bash
php artisan db:seed
```

### Seed Kelas Tertentu Saja
```bash
php artisan db:seed --class=UserCsvSeeder
php artisan db:seed --class=AttendanceCsvSeeder
```

## Data Testing yang Tersedia

### User Accounts
- **Admin**: admin@admin.com / password (role: admin)
- **Satpam**: john@satpam.com, jane@satpam.com, dll / password (role: satpam)

### Sample Attendance Data
- Status: on_time, late (dengan kategori terlambat_ringan, terlambat_sedang, terlambat_berat)  
- GPS validation: koordinat valid untuk setiap lokasi
- Face recognition: status approved dengan validation messages
- Check-in dan check-out times tersedia

### AI Predictions
- Risk scores: 0.1 - 0.9 (low to high risk)
- Reasons: analisis berbasis pola historis
- Predictions untuk tanggal 2025-08-23

### QR Codes  
- Active QR codes untuk semua kombinasi lokasi-shift
- Expiry dates yang valid
- Scan counts diinisialisasi ke 0

### Verifikasi Setup dengan Command
```bash
php artisan db:verify-setup
```

Command ini akan:
- ✅ Memverifikasi jumlah record setiap tabel
- 🔐 Mengecek data kunci (admin user, satpam, dll)
- 🔗 Memvalidasi relasi foreign key
- 📱 Menampilkan akun test yang tersedia

### Manual Verification
```bash
php artisan tinker --execute="
echo 'Users: ' . App\Models\User::count() . PHP_EOL;
echo 'Locations: ' . App\Models\Location::count() . PHP_EOL; 
echo 'Shifts: ' . App\Models\Shift::count() . PHP_EOL;
echo 'QR Codes: ' . App\Models\QrCode::count() . PHP_EOL;
echo 'Attendances: ' . App\Models\Attendance::count() . PHP_EOL;
echo 'AI Predictions: ' . App\Models\AiPrediction::count() . PHP_EOL;
echo 'Notifications: ' . App\Models\Notification::count() . PHP_EOL;
"
```

**Expected Output:**
```
Users: 8
Locations: 7
Shifts: 5
QR Codes: 15
Attendances: 15
AI Predictions: 10
Notifications: 6
```

## Keunggulan Setup Ini

1. **Konsistensi Data**: Data yang sama setiap kali setup
2. **Tes Fungsional Ready**: Semua fitur dapat langsung ditest
3. **Relasi Lengkap**: Foreign keys dan relationships sudah valid
4. **Real-world Scenarios**: Data mencakup berbagai case (tepat waktu, terlambat, dll)
5. **Easy Maintenance**: Edit CSV files untuk mengubah test data
6. **Version Control**: CSV files bisa di-track di git untuk konsistensi tim

## Troubleshooting

### CSV Format Issues
- Pastikan semua baris memiliki jumlah kolom yang sama
- Gunakan koma sebagai separator, hindari koma dalam data
- Format tanggal: YYYY-MM-DD HH:MM:SS
- Empty values: gunakan string kosong "", bukan null

### Database Constraints  
- Pastikan foreign keys valid (user_id, location_id, shift_id, qr_code_id)
- Enum values harus sesuai dengan definisi di migrasi
- Unique constraints akan dicek oleh updateOrCreate

### Performance
- Untuk dataset besar, pertimbangkan chunking atau batch insert
- Database indexes akan meningkatkan performa seeding
