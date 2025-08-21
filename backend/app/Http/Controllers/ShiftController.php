<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $locationId = $request->input('location_id');
        $dayOfWeek = $request->input('day_of_week');

        $shifts = $this->shiftService->getAllShifts($perPage, $locationId, $dayOfWeek);
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'location_id' => 'nullable|exists:locations,id',
            'active_days' => 'nullable|array',
            'active_days.*' => 'integer|between:1,7',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'nullable|in:active,inactive',
            'description' => 'nullable|string|max:500',
        ]);

        // Custom validation for shift times (allow overnight shifts)
        $validator->after(function ($validator) use ($request) {
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');

            if ($startTime && $endTime) {
                // If end time is before start time, it's an overnight shift (valid)
                // If end time equals start time, it's invalid (24 hour shift not allowed)
                if ($startTime === $endTime) {
                    $validator->errors()->add('end_time', 'End time cannot be the same as start time.');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $shift = $this->shiftService->createShift($data);

        return response()->json([
            'message' => 'Shift created successfully.',
            'shift' => $shift,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i',
            'location_id' => 'sometimes|nullable|exists:locations,id',
            'active_days' => 'sometimes|nullable|array',
            'active_days.*' => 'integer|between:1,7',
            'capacity' => 'sometimes|nullable|integer|min:1',
            'status' => 'sometimes|nullable|in:active,inactive',
            'description' => 'sometimes|nullable|string|max:500',
        ]);

        // Custom validation for shift times (allow overnight shifts)
        $validator->after(function ($validator) use ($request) {
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');

            if ($startTime && $endTime) {
                // If end time is before start time, it's an overnight shift (valid)
                // If end time equals start time, it's invalid (24 hour shift not allowed)
                if ($startTime === $endTime) {
                    $validator->errors()->add('end_time', 'End time cannot be the same as start time.');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
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
