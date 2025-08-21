<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TelegramNotificationController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Get bot information
     */
    public function getBotInfo()
    {
        $botInfo = $this->telegramService->getBotInfo();
        return response()->json($botInfo);
    }

    /**
     * Set webhook URL
     */
    public function setWebhook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'webhook_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->telegramService->setWebhook($request->webhook_url);

        return response()->json([
            'message' => 'Webhook set successfully',
            'result' => $result
        ]);
    }

    /**
     * Get users with Telegram integration
     */
    public function getTelegramUsers()
    {
        $users = User::withTelegram()
            ->select(['id', 'name', 'username', 'role', 'telegram_chat_id', 'telegram_username', 'telegram_notifications_enabled'])
            ->get();

        return response()->json($users);
    }

    /**
     * Send test notification to user
     */
    public function sendTestNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);
        if (!$user->telegram_chat_id) {
            return response()->json([
                'message' => 'User does not have Telegram integration enabled'
            ], 400);
        }

        $result = $this->telegramService->sendMessage(
            $user->telegram_chat_id,
            "🧪 *Test Notification*\n\n" . $request->message
        );

        return response()->json([
            'message' => $result ? 'Test notification sent successfully' : 'Failed to send notification',
            'success' => (bool) $result
        ]);
    }

    /**
     * Send broadcast message to all users
     */
    public function sendBroadcast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'target' => 'required|in:all,admins,users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = User::withTelegram();

        switch ($request->target) {
            case 'admins':
                $query->where('role', 'admin');
                break;
            case 'users':
                $query->where('role', '!=', 'admin');
                break;
            // 'all' doesn't need additional filtering
        }

        $users = $query->get();
        $successCount = 0;

        foreach ($users as $user) {
            $message = "📢 *Broadcast Message*\n\n" . $request->message;
            $result = $this->telegramService->sendMessage($user->telegram_chat_id, $message);
            if ($result) {
                $successCount++;
            }
        }

        return response()->json([
            'message' => "Broadcast sent to {$successCount} out of {$users->count()} users",
            'success_count' => $successCount,
            'total_count' => $users->count()
        ]);
    }

    /**
     * Send daily attendance report to admins
     */
    public function sendDailyReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date_format:Y-m-d',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $date = $request->input('date') ? 
            \Carbon\Carbon::createFromFormat('Y-m-d', $request->input('date')) : 
            now()->subDay();
        $locationId = $request->input('location_id');

        try {
            $result = $this->telegramService->sendDailyRecap($date, $locationId);

            if ($result) {
                return response()->json([
                    'message' => 'Daily report sent successfully to admin users',
                    'date' => $date->format('Y-m-d'),
                    'location_id' => $locationId
                ]);
            } else {
                return response()->json([
                    'message' => 'No admin users with Telegram integration found',
                    'date' => $date->format('Y-m-d'),
                    'location_id' => $locationId
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error sending daily report: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send daily report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user Telegram notifications
     */
    public function toggleNotifications(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $user->update([
            'telegram_notifications_enabled' => !$user->telegram_notifications_enabled
        ]);

        return response()->json([
            'message' => 'Notification settings updated successfully',
            'telegram_notifications_enabled' => $user->telegram_notifications_enabled
        ]);
    }
}
