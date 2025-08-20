<?php
namespace App\Services;

use App\Repositories\QrCodeRepository;
use Illuminate\Support\Str;

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
        return $this->qrCodeRepository->findQrCodeById($id);
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

}
