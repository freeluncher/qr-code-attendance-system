<?php
namespace App\Repositories;

use App\Models\QrCode;

class QrCodeRepository
{
    public function getAllQRCodes($perPage = 10)
    {
        return QrCode::with(['location', 'shift'])->paginate($perPage);
    }

    public function findQrCodeById($id)
    {
        return QrCode::with(['location', 'shift'])->findOrFail($id);
    }

    public function createQrCode(array $data)
    {
        return QrCode::create($data);
    }

    public function updateQrCode($id, array $data)
    {
        $qrCode = QrCode::findOrFail($id);
        $qrCode->update($data);
        return $qrCode;
    }

    public function deleteQrCode($id)
    {
        $qrCode = QrCode::findOrFail($id);
        return $qrCode->delete();
    }

}
