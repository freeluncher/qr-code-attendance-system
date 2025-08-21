<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle incoming Telegram webhook
     */
    public function handle(Request $request)
    {
        // Skip processing if this is a browser request (not from Telegram)
        $userAgent = $request->header('User-Agent', '');
        if (str_contains($userAgent, 'Mozilla') || str_contains($userAgent, 'Chrome') || str_contains($userAgent, 'Safari')) {
            Log::info('Webhook accessed from browser, returning simple response');
            return response()->json(['status' => 'webhook_active', 'message' => 'QR Attendance Telegram Bot Webhook']);
        }

        $update = $request->all();
        Log::info('Telegram webhook received', [
            'update' => $update,
            'user_agent' => $userAgent,
            'headers' => $request->headers->all()
        ]);

        try {
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            }

            return response()->json(['status' => 'ok'], 200, [
                'Content-Type' => 'application/json',
            ]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage(), [
                'update' => $update,
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }

    /**
     * Handle incoming messages
     */
    private function handleMessage($message)
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $username = $message['from']['username'] ?? null;

        // Handle /start command
        if (str_starts_with($text, '/start')) {
            $this->handleStartCommand($chatId, $username, $text);
            return;
        }

        // Handle /register command
        if (str_starts_with($text, '/register')) {
            $this->handleRegisterCommand($chatId, $username, $text);
            return;
        }

        // Handle /status command
        if (str_starts_with($text, '/status')) {
            $this->handleStatusCommand($chatId);
            return;
        }

        // Handle /help command
        if (str_starts_with($text, '/help')) {
            $this->handleHelpCommand($chatId);
            return;
        }

        // Default response
        $this->telegramService->sendMessage($chatId,
            "Perintah tidak dikenali. Ketik /help untuk melihat daftar perintah."
        );
    }

    /**
     * Handle /start command
     */
    private function handleStartCommand($chatId, $username, $text)
    {
        // Check if user already registered
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            $message = "✅ Selamat datang kembali, {$user->name}!\n\n" .
                      "Anda sudah terdaftar di sistem QR Code Attendance.\n" .
                      "Ketik /help untuk melihat perintah yang tersedia.";
        } else {
            $message = "👋 Selamat datang di *QR Code Attendance System*!\n\n" .
                      "Untuk mendaftarkan akun Telegram Anda dengan sistem absensi, " .
                      "gunakan perintah:\n\n" .
                      "`/register [username_anda]`\n\n" .
                      "Contoh: `/register john_doe`\n\n" .
                      "Username adalah username yang Anda gunakan di sistem absensi.";
        }

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Handle /register command
     */
    private function handleRegisterCommand($chatId, $telegramUsername, $text)
    {
        $parts = explode(' ', $text, 2);

        if (count($parts) < 2) {
            $message = "❌ Format salah!\n\n" .
                      "Gunakan: `/register [username_anda]`\n" .
                      "Contoh: `/register john_doe`";
            $this->telegramService->sendMessage($chatId, $message);
            return;
        }

        $username = trim($parts[1]);

        // Find user by username
        $user = User::where('username', $username)->first();

        if (!$user) {
            $message = "❌ Username '{$username}' tidak ditemukan!\n\n" .
                      "Pastikan username Anda sudah terdaftar di sistem absensi.";
            $this->telegramService->sendMessage($chatId, $message);
            return;
        }

        // Check if user already has Telegram linked
        if ($user->telegram_chat_id) {
            $message = "⚠️ Username '{$username}' sudah terhubung dengan Telegram lain.\n\n" .
                      "Hubungi admin jika ini adalah akun Anda.";
            $this->telegramService->sendMessage($chatId, $message);
            return;
        }

        // Link Telegram to user
        $user->update([
            'telegram_chat_id' => $chatId,
            'telegram_username' => $telegramUsername,
            'telegram_notifications_enabled' => true,
        ]);

        $message = "✅ *Registrasi Berhasil!*\n\n" .
                  "👤 Nama: {$user->name}\n" .
                  "👨‍💼 Role: " . ucfirst($user->role) . "\n" .
                  "📱 Username: {$user->username}\n\n" .
                  "Anda akan menerima notifikasi untuk:\n" .
                  "• Konfirmasi absen masuk/keluar\n" .
                  "• Pengingat shift\n" .
                  "• Peringatan keterlambatan\n" .
                  ($user->isAdmin() ? "• Laporan admin\n" : "") .
                  "\nKetik /help untuk perintah lainnya.";

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Handle /status command
     */
    private function handleStatusCommand($chatId)
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $message = "❌ Anda belum terdaftar!\n\n" .
                      "Gunakan `/register [username_anda]` untuk mendaftar.";
            $this->telegramService->sendMessage($chatId, $message);
            return;
        }

        // Get today's attendance
        $todayAttendance = $user->attendances()
            ->whereDate('scanned_at', today())
            ->with(['location', 'shift'])
            ->first();

        $message = "📊 *Status Kehadiran Hari Ini*\n\n" .
                  "👤 Nama: {$user->name}\n" .
                  "📅 Tanggal: " . now()->format('d/m/Y') . "\n\n";

        if ($todayAttendance) {
            $message .= "✅ *Sudah Absen*\n" .
                       "📍 Lokasi: " . ($todayAttendance->location->name ?? 'Unknown') . "\n" .
                       "🕐 Shift: " . ($todayAttendance->shift->name ?? 'No Shift') . "\n" .
                       "⏰ Masuk: " . $todayAttendance->scanned_at->format('H:i') . "\n";

            if ($todayAttendance->check_out_time) {
                $message .= "🚪 Keluar: " . $todayAttendance->check_out_time->format('H:i') . "\n";
            } else {
                $message .= "🚪 Keluar: Belum absen keluar\n";
            }

            $message .= "📊 Status: " . ($todayAttendance->is_late ? "🔴 TERLAMBAT" : "✅ TEPAT WAKTU");
        } else {
            $message .= "❌ *Belum Absen Hari Ini*\n" .
                       "Jangan lupa untuk melakukan absen! 🎯";
        }

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Handle /help command
     */
    private function handleHelpCommand($chatId)
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        $message = "📖 *Daftar Perintah*\n\n";

        if (!$user) {
            $message .= "🔐 **Untuk Pengguna Baru:**\n" .
                       "• `/start` - Mulai menggunakan bot\n" .
                       "• `/register [username]` - Daftar dengan username sistem\n\n";
        } else {
            $message .= "👤 **Untuk Pengguna Terdaftar:**\n" .
                       "• `/status` - Cek status kehadiran hari ini\n" .
                       "• `/help` - Tampilkan pesan ini\n\n" .
                       "📱 **Notifikasi Otomatis:**\n" .
                       "• Konfirmasi absen masuk/keluar\n" .
                       "• Pengingat shift (30 menit sebelumnya)\n" .
                       "• Peringatan keterlambatan\n";

            if ($user->isAdmin()) {
                $message .= "• Laporan admin (keterlambatan, tidak hadir)\n";
            }
        }

        $message .= "\n💡 **Tips:**\n" .
                   "• Pastikan notifikasi Telegram aktif\n" .
                   "• Hubungi admin jika ada masalah\n" .
                   "• Username harus sama dengan sistem absensi";

        $this->telegramService->sendMessage($chatId, $message);
    }
}
