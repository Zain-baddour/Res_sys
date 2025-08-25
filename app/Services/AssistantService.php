<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\DeviceToken;
use App\Models\hall;
use App\Models\hall_employee;
use App\Models\HallContact;
use App\Models\hallEventImages;
use App\Models\hallEventVideos;
use App\Models\inquiry;
use App\Models\inquiryResponse;
use App\Models\staff_requests;
use App\Models\User;

class AssistantService
{
    public function responseToInquiry($data) {
        $res = inquiryResponse::create($data);

        $notify = inquiry::where('id',$data['inquiry_id'])->value('user_id');
        $clientTokens = DeviceToken::where('user_id', $notify)->pluck('device_token');

        $firebase = new FirebaseNotificationService();

        foreach ($clientTokens as $token) {
            $firebase->sendNotification(
                $token,
                "response received",
                "{$data['response']}"
            );
        }

        return $res;
    }

    public function requestStaff($data) {

        $req = staff_requests::create($data);
        $hall = hall::findOrFail($data['hall_id']);
        $userId = auth()->id();
        $user = User::findOrFail($userId);
        $clientTokens = DeviceToken::where('user_id', $hall->owner_id)->pluck('device_token');

        $firebase = new FirebaseNotificationService();

        foreach ($clientTokens as $token) {
            $firebase->sendNotification(
                $token,
                "Your have a staff request",
                "user {$user->name} has requested to be an employee in your hall {$hall->name}"
            );
        }


        return $req;

    }

    public function getStaffRequest() {
        $userId = auth()->id();
        return staff_requests::where('user_id', $userId)->with(['hall'])->get();
    }

    public function getChats() {
        $assistantId = auth()->id();

        $hallId = hall_employee::where('user_id', $assistantId)->value('hall_id');
        if(!$hallId) {
            return response()->json(['message' => 'you are not employed'], 404);
        }

        $customers = inquiry::where('hall_id', $hallId)
            ->distinct()
            ->pluck('user_id');

        if ($customers->isEmpty()) {
            return response()->json(['message' => 'no messages yet!'], 404);
        }

        $customersDetails = User::whereIn('id', $customers)->get();

        return $customersDetails ;
    }

    public function uploadEventImages($data) {
        $assistantId = auth()->id();

        $hallId = hall_employee::where('user_id', $assistantId)->value('hall_id');
        if(!$hallId) {
            return response()->json(['message' => 'you are not employed'], 404);
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
            return response()->json(['message' => 'you are not employed'], 404);
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

    public function uploadContact($data) {
        $assistantId = auth()->id();

        $hallId = hall_employee::where('user_id', $assistantId)->value('hall_id');
        if(!$hallId) {
            return response()->json(['message' => 'you are not employed'], 404);
        }

        $contact = HallContact::updateOrCreate([
            'user_id' => $assistantId,
            'hall_id' => $hallId,
            'telegram' => $data['telegram'],
            'whatsUp' => $data['whatsUp'],
        ]);
        return $contact;

    }

    public function costumeSearchBooking(array $filters) {
        $query = Booking::query();

        if(!empty($filters['user_id'])) {
            $query->where('user_id', '=',$filters['user_id']);
        }

        if(!empty($filters['event_date'])) {
            $query->where('event_date', '=',$filters['event_date']);
        }


        return $query->get();
    }

}
