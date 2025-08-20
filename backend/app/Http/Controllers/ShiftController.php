<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ShiftService;

class ShiftController extends Controller
{
    protected $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $shifts = $this->shiftService->getAllShifts($perPage);
        return response()->json($shifts);
    }

    public function show($id)
    {
        $shift = $this->shiftService->getShiftById($id);
        if (!$shift) {
            return response()->json(['message' => 'Shift not found'], 404);
        }
        return response()->json($shift);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);
        $shift = $this->shiftService->createShift($data);

        return response()->json([
            'message' => 'Shift created successfully.',
            'shift' => $shift,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
        ]);
        $shift = $this->shiftService->updateShift($id, $data);

        return response()->json([
            'message' => 'Shift updated successfully.',
            'shift' => $shift,
        ]);
    }

    public function destroy($id)
    {
        $this->shiftService->deleteShift($id);
        return response()->json(['message' => 'Shift deleted successfully']);
    }
}
