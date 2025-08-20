<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FaceRecognitionService
{
    private $faceApiUrl;

    public function __construct()
    {
        $this->faceApiUrl = config('services.face_api.url', 'http://localhost:8001');
    }

    /**
     * Detect face landmarks in image
     */
    public function detectFace(UploadedFile $image)
    {
        try {
            $response = Http::attach('file', $image->get(), $image->getClientOriginalName())
                ->post($this->faceApiUrl . '/detect-face');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'message' => 'Failed to detect face: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Face detection error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Face recognition service unavailable'
            ];
        }
    }

    /**
     * Register reference face for user
     */
    public function registerFace(int $userId, UploadedFile $image)
    {
        try {
            $response = Http::attach('file', $image->get(), $image->getClientOriginalName())
                ->post($this->faceApiUrl . '/register-face', [
                    'user_id' => $userId
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'message' => 'Failed to register face: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Face registration error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Face recognition service unavailable'
            ];
        }
    }

    /**
     * Verify face against registered reference
     */
    public function verifyFace(int $userId, UploadedFile $image)
    {
        try {
            $response = Http::attach('file', $image->get(), $image->getClientOriginalName())
                ->post($this->faceApiUrl . '/verify-face', [
                    'user_id' => $userId
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'message' => 'Failed to verify face: ' . $response->body(),
                'match' => false
            ];

        } catch (\Exception $e) {
            Log::error('Face verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Face recognition service unavailable',
                'match' => false
            ];
        }
    }

    /**
     * Store attendance photo
     */
    public function storeAttendancePhoto(UploadedFile $image, int $userId, string $type = 'check_in')
    {
        $filename = 'attendance_' . $userId . '_' . $type . '_' . now()->format('Y_m_d_H_i_s') . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('attendance_photos', $filename, 'public');
        
        return $path;
    }
}
