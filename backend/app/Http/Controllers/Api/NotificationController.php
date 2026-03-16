<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function lister(Request $request): JsonResponse
    {
        $listeNotifications = $request->user()
            ->notifications()
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($notificationItem) {
                return [
                    'id' => $notificationItem->id,
                    'type' => $notificationItem->type,
                    'data' => $notificationItem->data,
                    'read_at' => optional($notificationItem->read_at)?->toISOString(),
                    'created_at' => optional($notificationItem->created_at)?->toISOString(),
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Notifications recuperees avec succes.',
            'data' => $listeNotifications,
        ]);
    }

    public function marquerLue(Request $request, string $idNotification): JsonResponse
    {
        $notificationTrouvee = $request->user()->notifications()->whereKey($idNotification)->first();
        if (! $notificationTrouvee) {
            return response()->json(['message' => 'Notification introuvable.'], 404);
        }

        if ($notificationTrouvee->read_at === null) {
            $notificationTrouvee->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marquee comme lue.',
        ]);
    }

    public function toutMarquerLu(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'Toutes les notifications ont ete marquees comme lues.',
        ]);
    }
}
