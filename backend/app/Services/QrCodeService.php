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
        // Generate kode unik dan set expired 1 jam dari sekarang
        $data['code'] = Str::random(16);
        $data['expires_at'] = now()->addHour();
        $qrCode = $this->qrCodeRepository->createQrCode($data);
        return $qrCode;
    }

    public function updateQrCode($id, array $data)
    {
        return $this->qrCodeRepository->updateQrCode($id, $data);
    }

    public function deleteQrCode($id)
    {
        return $this->qrCodeRepository->deleteQrCode($id);
    }

}
