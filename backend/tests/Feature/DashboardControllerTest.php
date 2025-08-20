<?php

use Tests\TestCase;
use App\Services\DashboardService;
use App\Repositories\DashboardRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $dashboardService;
    protected $dashboardRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardRepository = new DashboardRepository();
        $this->dashboardService = new DashboardService($this->dashboardRepository);
    }

    /** @test */
    public function it_can_get_admin_stats()
    {
        // Test that admin stats method returns expected structure
        $response = $this->get('/api/dashboard/stats');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'total_satpam',
                    'total_locations',
                    'total_qr_codes',
                    'today' => [
                        'total_attendance',
                        'late_count',
                        'on_time_count',
                        'attendance_rate'
                    ],
                    'this_week' => [
                        'total_attendance'
                    ],
                    'this_month' => [
                        'total_attendance',
                        'avg_daily_attendance'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_recent_activities()
    {
        $response = $this->get('/api/dashboard/activities');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'user_name',
                            'action',
                            'location_name',
                            'status',
                            'created_at',
                            'formatted_time'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_top_late_employees()
    {
        $response = $this->get('/api/dashboard/late-employees');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'user_id',
                            'name',
                            'location',
                            'late_count'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_attendance_chart_data()
    {
        $response = $this->get('/api/dashboard/attendance-chart');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'date',
                            'day_name',
                            'formatted_date',
                            'total',
                            'on_time',
                            'late',
                            'on_time_percentage'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function dashboard_service_is_properly_bound()
    {
        $service = app(DashboardService::class);
        $this->assertInstanceOf(DashboardService::class, $service);

        // Test that service has repository dependency
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('dashboardRepository');
        $property->setAccessible(true);
        $repository = $property->getValue($service);

        $this->assertInstanceOf(DashboardRepository::class, $repository);
    }

    /** @test */
    public function dashboard_repository_methods_exist()
    {
        $repository = app(DashboardRepository::class);

        // Test that all required methods exist
        $this->assertTrue(method_exists($repository, 'getTotalSatpamCount'));
        $this->assertTrue(method_exists($repository, 'getTotalLocationsCount'));
        $this->assertTrue(method_exists($repository, 'getActiveQrCodesCount'));
        $this->assertTrue(method_exists($repository, 'getTodayAttendances'));
        $this->assertTrue(method_exists($repository, 'getRecentActivities'));
        $this->assertTrue(method_exists($repository, 'getTopLateEmployees'));
        $this->assertTrue(method_exists($repository, 'getUserTodayAttendance'));
        $this->assertTrue(method_exists($repository, 'getUserMonthlyAttendances'));
        $this->assertTrue(method_exists($repository, 'getUserAttendanceHistory'));
    }
}
