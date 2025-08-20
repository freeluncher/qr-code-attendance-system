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
        ]);

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
}
