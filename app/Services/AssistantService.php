<?php

namespace App\Services;

use App\Models\hall;
use App\Models\hall_employee;
use App\Models\inquiry;
use App\Models\inquiryResponse;
use App\Models\staff_requests;
use App\Models\User;

class AssistantService
{
    public function responseToInquiry($data) {
        return inquiryResponse::create($data);
    }

    public function requestStaff($data) {
        return staff_requests::create($data);
    }

    public function getChats() {
        $assistantId = auth()->id();

        $hallId = hall_employee::where('user_id', $assistantId)->value('hall_id');
        if(!$hallId) {
            return response()->json(['message' => 'أنت غير مرتبط بقاعة'], 404);
        }

        $customers = inquiry::where('hall_id', $hallId)
            ->distinct()
            ->pluck('user_id');

        if ($customers->isEmpty()) {
            return response()->json(['message' => 'لا توجد رسائل حاليا'], 404);
        }

        $customersDetails = User::whereIn('id', $customers)->get();

        return $customersDetails ;
    }

}
