<?php
namespace App\Services;

use App\Repositories\QrCodeRepository;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService{
    protected $qrCodeRepository;

    public function __construct(QrCodeRepository $qrCodeRepository)
    {
        $this->qrCodeRepository = $qrCodeRepository;
    }

    public function getAllQRCodes($perPage = 10)
    {
        return $this->qrCodeRepository->getAllQRCodes($perPage);
    }

    public function getQrCodeById($id)
    {
        $qrCode = $this->qrCodeRepository->findQrCodeById($id);
        
        // Generate QR Code image if requested
        $qrCodeData = [
            'id' => $qrCode->id,
            'code' => $qrCode->code,
            'location_id' => $qrCode->location_id,
            'shift_id' => $qrCode->shift_id,
            'expires_at' => $qrCode->expires_at,
        ];
        
        $qrCodeImage = QrCode::format('png')
            ->size(300)
            ->margin(10)
            ->generate(json_encode($qrCodeData));
            
        // Convert to base64 for easy frontend display
        $qrCode->qr_image = 'data:image/png;base64,' . base64_encode($qrCodeImage);
        
        return $qrCode;
    }

    public function generateQrCode($data)
    {
        // Generate UUID untuk code yang unik
        $data['code'] = Str::uuid();

        // Set expires_at dari input, atau default 24 jam dari sekarang
        if (!isset($data['expires_at'])) {
            $data['expires_at'] = now()->addHours(24);
        }

        $qrCode = $this->qrCodeRepository->createQrCode($data);
        
        // Generate QR Code image as base64
        $qrCodeData = [
            'id' => $qrCode->id,
            'code' => $qrCode->code,
            'location_id' => $qrCode->location_id,
            'shift_id' => $qrCode->shift_id,
            'expires_at' => $qrCode->expires_at,
        ];
        
        $qrCodeImage = QrCode::format('png')
            ->size(300)
            ->margin(10)
            ->generate(json_encode($qrCodeData));
            
        // Convert to base64 for easy frontend display
        $qrCode->qr_image = 'data:image/png;base64,' . base64_encode($qrCodeImage);
        
        return $qrCode;
    }

    public function updateQrCode($id, $data)
    {
        // Jika expires_at tidak diset, gunakan nilai yang ada
        $qrCode = $this->qrCodeRepository->findQrCodeById($id);

        if (!isset($data['expires_at'])) {
            $data['expires_at'] = $qrCode->expires_at;
        }

        return $this->qrCodeRepository->updateQrCode($id, $data);
    }

    public function deleteQrCode($id)
    {
        return $this->qrCodeRepository->deleteQrCode($id);
    }

    public function generateQrCodeImage($qrCode, $size = 300)
    {
        $qrCodeData = [
            'id' => $qrCode->id,
            'code' => $qrCode->code,
            'location_id' => $qrCode->location_id,
            'shift_id' => $qrCode->shift_id,
            'expires_at' => $qrCode->expires_at,
        ];
        
        $qrCodeImage = QrCode::format('png')
            ->size($size)
            ->margin(10)
            ->generate(json_encode($qrCodeData));
            
        return 'data:image/png;base64,' . base64_encode($qrCodeImage);
    }

}
