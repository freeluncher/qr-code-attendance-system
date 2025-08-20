<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\QrCodeService;
use PhpParser\Node\Stmt\TryCatch;
class QrCodeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $qrcodes = $this->qrCodeService->getAllQRCodes($perPage);
        return response()->json($qrcodes);
    }

    public function show($id)
    {
        try {
            $qrcode = $this->qrCodeService->getQrCodeById($id);
            return response()->json($qrcode);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'QR Code not found.'], 404);
        }


    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'shift_id' => 'required|exists:shifts,id',
            'expires_in_hours' => 'sometimes|integer|min:1|max:168', // Max 7 days
        ]);

        // Set expires_at based on hours input (default 24 hours)
        $hoursToExpire = $data['expires_in_hours'] ?? 24;
        $data['expires_at'] = now()->addHours($hoursToExpire);

        // Remove expires_in_hours from data since it's not a model field
        unset($data['expires_in_hours']);

        $qrcode = $this->qrCodeService->generateQrCode($data);

        return response()->json([
            'message' => 'QR Code created successfully.',
            'qrcode' => $qrcode,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'location_id' => 'sometimes|required|exists:locations,id',
            'shift_id' => 'sometimes|required|exists:shifts,id',
            'expires_at' => 'sometimes|required|date',
        ]);

        $qrcode = $this->qrCodeService->updateQrCode($id, $data);

        return response()->json([
            'message' => 'QR Code updated successfully.',
            'qrcode' => $qrcode,
        ]);
    }

    public function destroy($id)
    {
        $this->qrCodeService->deleteQrCode($id);
        return response()->json(['message' => 'QR Code deleted successfully.'], 200);
    }

    public function renew(Request $request, $id)
    {
        $data = $request->validate([
            'duration_days' => 'sometimes|integer|min:1|max:365',
            'expires_in_hours' => 'sometimes|integer|min:1|max:8760', // Max 365 days
        ]);

        if (isset($data['expires_in_hours'])) {
            // Use hours for more precise control
            $newExpiresAt = now()->addHours($data['expires_in_hours']);
        } else {
            // Default to days for backward compatibility
            $durationDays = $data['duration_days'] ?? 30; // Default 30 days
            $newExpiresAt = now()->addDays($durationDays);
        }

        $qrcode = $this->qrCodeService->updateQrCode($id, [
            'expires_at' => $newExpiresAt
        ]);

        return response()->json([
            'message' => 'QR Code renewed successfully.',
            'qrcode' => $qrcode,
        ]);
    }

    public function generateImage($id)
    {
        try {
            $qrcode = $this->qrCodeService->getQrCodeById($id);
            $qrImage = $this->qrCodeService->generateQrCodeImage($qrcode);

            return response()->json([
                'qr_image' => $qrImage,
                'qrcode' => $qrcode,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'QR Code not found.'], 404);
        }
    }
}
