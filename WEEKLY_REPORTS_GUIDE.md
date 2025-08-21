# Weekly Reports - QR Code Attendance System

## Deskripsi
Fitur Weekly Reports memungkinkan admin untuk:
- ✅ **Ekspor laporan mingguan per lokasi** dengan data nama, tanggal, jam scan, dan status (On-Time/Late/Absent)
- ✅ **Schedule otomatis (cron) ke email** setiap hari Senin pagi
- ✅ **Manajemen laporan** melalui web interface
- ✅ **Kirim laporan via email** secara manual atau otomatis

## Struktur Database

### Weekly Reports Table
```sql
- id (Primary Key)
- location_id (Foreign Key to locations)
- week_start_date (Date)
- week_end_date (Date) 
- report_data (JSON) - Data lengkap attendance
- file_path (String) - Path ke file CSV
- email_sent_at (Timestamp) - Kapan email dikirim
- created_by (Foreign Key to users)
- timestamps
```

## Backend Implementation

### 1. Model & Service
- **WeeklyReport Model** (`app/Models/WeeklyReport.php`)
- **WeeklyReportService** (`app/Services/WeeklyReportService.php`)
  - Generate data laporan mingguan
  - Export ke CSV
  - Perhitungan status (On-Time/Late/Absent)

### 2. Console Command
**Command:** `php artisan reports:weekly`

**Options:**
```bash
--location=ID          # Generate untuk lokasi tertentu
--week=2025-01-06      # Tanggal awal minggu (default: minggu lalu)
--send-email           # Kirim via email otomatis
--email=test@mail.com  # Email tujuan spesifik
```

**Examples:**
```bash
# Generate semua lokasi minggu lalu
php artisan reports:weekly --send-email

# Generate lokasi tertentu
php artisan reports:weekly --location=1 --week=2025-01-06 --send-email

# Generate dan kirim ke email spesifik
php artisan reports:weekly --email=manager@company.com --send-email
```

### 3. Scheduling (Cron Jobs)
Di `routes/console.php`:

```php
// Otomatis setiap Senin jam 7:00 AM
Schedule::command('reports:weekly --send-email')
    ->weeklyOn(1, '07:00')
    ->name('weekly-reports-auto');

// Generate per lokasi setiap Senin jam 8:00 AM
Schedule::call(function () {
    $locations = \App\Models\Location::where('is_active', true)->get();
    foreach ($locations as $location) {
        Artisan::call('reports:weekly', [
            '--location' => $location->id,
            '--send-email' => true,
        ]);
    }
})->weeklyOn(1, '08:00');
```

### 4. API Endpoints
Base URL: `/api/weekly-reports`

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/` | List semua laporan |
| POST | `/` | Generate laporan baru |
| GET | `/{id}` | Detail laporan |
| GET | `/{id}/download` | Download CSV |
| POST | `/{id}/send-email` | Kirim via email |
| DELETE | `/{id}` | Hapus laporan |
| GET | `/locations` | List lokasi aktif |
| POST | `/preview` | Preview tanpa save |

### 5. Email System
- **Mailable:** `WeeklyReportMail`
- **Job:** `SendWeeklyReportEmail` (Queue support)
- **Template:** `resources/views/emails/weekly-report.blade.php`

## Frontend Implementation

### 1. Component
**File:** `frontend/src/views/WeeklyReports.vue`

**Features:**
- ✅ List laporan dengan filter
- ✅ Generate laporan baru
- ✅ Download CSV
- ✅ Kirim email
- ✅ Preview data
- ✅ Delete laporan

### 2. Routes
```javascript
{
  path: '/admin/weekly-reports',
  name: 'admin-weekly-reports',
  component: WeeklyReports,
  meta: { requiresAuth: true, requiresRole: 'admin' }
}
```

## Format Data CSV

### Header CSV
```csv
Weekly Attendance Report
Location: [Nama Lokasi]
Address: [Alamat Lokasi]
Week Period: 2025-01-06 to 2025-01-12

SUMMARY
Total Employees,15
On-Time Attendance,52
Late Attendance,8
Absent,15

DETAILED ATTENDANCE
Name,Email,Date,Day,Scan Time,Checkout Time,Status,Shift,Late Minutes
"John Doe","john@example.com","2025-01-06","Monday","07:30:00","16:00:00","On-Time","Shift Pagi",0
"Jane Smith","jane@example.com","2025-01-06","Monday","08:15:00","16:30:00","Late","Shift Pagi",45
```

## Status Calculation Logic

### 1. Status Types
- **On-Time:** Scan ≤ 30 menit setelah shift dimulai
- **Late:** Scan > 30 menit setelah shift dimulai  
- **Absent:** Tidak ada scan di hari kerja
- **Weekend:** Hari libur (Sabtu/Minggu)
- **No Shift:** User tidak memiliki shift

### 2. Late Minutes Calculation
```php
$lateMinutes = $scanTime > $shiftStartTime + 30min ? 
    $scanTime->diffInMinutes($shiftStartTime) : 0;
```

## Email Configuration

### Environment Variables
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourcompany.com"
MAIL_FROM_NAME="QR Attendance System"

# Admin email untuk weekly reports
MAIL_ADMIN_EMAIL=admin@yourcompany.com
```

### Email Template Features
- ✅ Responsive HTML design
- ✅ Summary statistics (badges)
- ✅ Detailed attendance table
- ✅ CSV file attachment
- ✅ Professional styling

## Setup & Installation

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Configure Email
Update `.env` file dengan konfigurasi SMTP.

### 3. Test Commands
```bash
# Test generate report
php artisan reports:weekly --location=1 --week=2025-01-06

# Test dengan email
php artisan reports:weekly --send-email --email=test@example.com
```

### 4. Setup Cron Job (Production)
```bash
# Edit crontab
crontab -e

# Add Laravel scheduler
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Queue Configuration (Optional)

### 1. Update Queue Driver
```bash
# .env
QUEUE_CONNECTION=database
```

### 2. Run Queue Worker
```bash
php artisan queue:work
```

## Error Handling

### 1. Common Errors
- **No attendance data:** Report tetap dibuat dengan status Absent
- **Missing shift:** Status "No Shift"
- **Email failure:** Job retry otomatis 3x
- **File not found:** Regenerate laporan

### 2. Logging
Semua proses tercatat di `storage/logs/laravel.log`:
- Report generation
- Email sending
- Errors and failures

## Testing

### 1. Manual Testing
```bash
# Generate test data
php artisan db:seed

# Test report generation
php artisan reports:weekly --location=1

# Test email (gunakan mailtrap.io untuk testing)
php artisan reports:weekly --send-email --email=test@mailtrap.io
```

### 2. API Testing
```bash
# Generate report via API
curl -X POST http://localhost:8000/api/weekly-reports \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"location_id":1,"week_start_date":"2025-01-06","send_email":true}'

# Download CSV
curl -X GET http://localhost:8000/api/weekly-reports/1/download \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output report.csv
```

## Performance Considerations

### 1. Large Datasets
- Reports dijalankan di background (Queue)
- CSV files disimpan di storage
- Pagination untuk list reports

### 2. Memory Usage
- Batch processing untuk lokasi besar
- Cleanup file CSV lama secara periodik

### 3. Email Rate Limits
- Queue untuk menghindari spam limits
- Retry mechanism untuk failed emails

## Production Checklist

- [ ] ✅ Configure SMTP email settings
- [ ] ✅ Set up cron job for scheduling
- [ ] ✅ Configure queue worker for background jobs
- [ ] ✅ Test email delivery
- [ ] ✅ Set appropriate file storage permissions
- [ ] ✅ Configure email rate limits
- [ ] ✅ Set up log monitoring
- [ ] ✅ Test with real attendance data

## Troubleshooting

### Email Not Sending
1. Check SMTP configuration
2. Verify authentication credentials
3. Check firewall/port blocking
4. Test dengan mailtrap.io atau mailhog

### Reports Not Generating
1. Check database connectivity
2. Verify user permissions on storage folder
3. Check attendance data exists
4. Review laravel.log for errors

### Cron Not Running
1. Verify crontab setup
2. Check PHP CLI path
3. Verify project path is correct
4. Check file permissions

## Support & Maintenance

### Regular Maintenance
- Cleanup old CSV files (monthly)
- Monitor email delivery rates
- Review failed job queue
- Update email templates as needed

### Performance Monitoring
- Monitor report generation time
- Track email delivery success rate  
- Monitor storage usage for CSV files
- Review error logs regularly
