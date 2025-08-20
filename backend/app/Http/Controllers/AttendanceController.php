<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $attendances = $this->attendanceService->getAllAttendances($perPage);
        return response()->json($attendances);
    }

    public function show($id)
    {
        $attendance = $this->attendanceService->getAttendanceById($id);
        return response()->json($attendance);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'qr_code'      => 'required|string|exists:qr_codes,code',
            'latitude'     => 'required|numeric',
            'longitude'    => 'required|numeric',
            'photo'        => 'required|image|max:2048',
            'late_category'=> 'nullable|string',
        ]);
        $user = Auth::user();
        try {
            $attendance = $this->attendanceService->createAttendance($data, $user);
            return response()->json([
                'message' => 'Presensi berhasil',
                'attendance' => $attendance,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
