<?php

namespace App\Services;

use App\Models\hall;
use App\Models\inquiry;

class ClientService
{
    public function createInquiry($data) {
        return inquiry::create($data);
    }

    public function getMyInquiries($userId) {
        $inquiries = Inquiry::with(['responses' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        $result = [];

        foreach ($inquiries as $inquiry) {
            $conversation = [];

            // أضف الاستفسار الأساسي
            $conversation[] = [
                'type' => 'inquiry',
                'message' => $inquiry->message,
                'sender_id' => $inquiry->user_id,
                'created_at' => $inquiry->created_at->toDateTimeString(),
            ];

            // أضف الردود المرتبة
            foreach ($inquiry->responses as $response) {
                $conversation[] = [
                    'type' => 'response',
                    'message' => $response->response,
                    'sender_id' => $response->user_id,
                    'created_at' => $response->created_at->toDateTimeString(),
                ];
            }

            // دمج المحادثة مع النتيجة النهائية
            $result[] = [
                'hall_id' => $inquiry->hall_id,
                'inquiry_id' => $inquiry->id,
                'conversation' => $conversation,
            ];
        }

        return $result;
    }

}
