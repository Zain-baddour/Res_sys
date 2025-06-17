<?php

namespace App\Services;

use App\Models\hall;
use App\Models\hall_employee;
use App\Models\hallEventImages;
use App\Models\hallEventVideos;
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

    public function getStaffRequest() {
        $userId = auth()->id();
        return staff_requests::where('user_id', $userId)->get();
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

    public function uploadEventImages($data) {
        $assistantId = auth()->id();

        $hallId = hall_employee::where('user_id', $assistantId)->value('hall_id');
        if(!$hallId) {
            return response()->json(['message' => 'أنت غير مرتبط بقاعة'], 404);
        }

        $hall = hall::where('id', $hallId)->get();
        if (isset($data['images'])) {
            foreach ($data['images'] as $image) {
                $imageName = uniqid() . '_hall_event_images_.' . $image->getClientOriginalExtension();
                $path = $image->move(public_path(), $imageName);
                $eventImage = hallEventImages::create([
                    'image_path' => $imageName,
                    'hall_id' => $hallId,
                    'event_type' => $data['event_type'],
                ]);

            }
        }

        return $hall->load('eventImages');

    }

    public function uploadEventVideos($data) {
        $assistantId = auth()->id();

        $hallId = hall_employee::where('user_id', $assistantId)->value('hall_id');
        if(!$hallId) {
            return response()->json(['message' => 'أنت غير مرتبط بقاعة'], 404);
        }

        $hall = hall::where('id', $hallId)->get();
        if (isset($data['video'])) {
            foreach ($data['video'] as $video) {
                $videoName = uniqid() . '_hall_event_video_.' . $video->getClientOriginalExtension();
                $path = $video->move(public_path(), $videoName);
                hallEventVideos::create([
                    'video_path' => $videoName,
                    'hall_id' => $hallId,
                    'event_type' => $data['event_type'],
                ]);
            }
        }
        return $hall->load('eventVideos');
    }

}
