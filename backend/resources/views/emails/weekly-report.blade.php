<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }
        .stat-item {
            text-align: center;
            background: white;
            padding: 10px;
            border-radius: 6px;
            min-width: 120px;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
        }
        .stat-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #34495e;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-on-time {
            background-color: #d4edda;
            color: #155724;
        }
        .status-late {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-absent {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-weekend {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 14px;
            color: #7f8c8d;
        }
        .attachment-info {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        @media (max-width: 600px) {
            .stats {
                flex-direction: column;
            }
            .stat-item {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Weekly Attendance Report</h2>
        <p><strong>Location:</strong> {{ $location->name }}</p>
        <p><strong>Address:</strong> {{ $location->address }}</p>
        <p><strong>Week Period:</strong> {{ $weekPeriod }}</p>
        <p><strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}</p>
    </div>

    <div class="summary">
        <h3>Weekly Summary</h3>
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number">{{ $reportData['summary']['total_employees'] }}</div>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $reportData['summary']['on_time'] }}</div>
                <div class="stat-label">On-Time</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $reportData['summary']['late'] }}</div>
                <div class="stat-label">Late</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $reportData['summary']['absent'] }}</div>
                <div class="stat-label">Absent</div>
            </div>
        </div>
    </div>

    <h3>Detailed Attendance</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Scan Time</th>
                    <th>Checkout</th>
                    <th>Status</th>
                    <th>Shift</th>
                    <th>Late (min)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['attendances'] as $userAttendance)
                    @foreach($userAttendance['daily_status'] as $dailyStatus)
                        <tr>
                            <td>{{ $userAttendance['name'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($dailyStatus['date'])->format('d M Y') }}</td>
                            <td>{{ $dailyStatus['day_name'] }}</td>
                            <td>{{ $dailyStatus['scan_time'] ?? '-' }}</td>
                            <td>{{ $dailyStatus['checkout_time'] ?? '-' }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower(str_replace('-', '-', $dailyStatus['status'])) }}">
                                    {{ $dailyStatus['status'] }}
                                </span>
                            </td>
                            <td>{{ $dailyStatus['shift_name'] }}</td>
                            <td>{{ $dailyStatus['late_minutes'] > 0 ? $dailyStatus['late_minutes'] : '-' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="attachment-info">
        <strong>📎 CSV File Attached</strong>
        <p>The complete attendance data is available in the attached CSV file for further analysis or record keeping.</p>
    </div>

    <div class="footer">
        <p>This report was automatically generated by the QR Code Attendance System.</p>
        <p>If you have any questions about this report, please contact the system administrator.</p>
    </div>
</body>
</html>
