<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Complaint;
use App\Models\DeviceToken;
use App\Models\hall;
use App\Models\inquiry;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CommentAnalyzer;


class ClientService
{


    public function createInquiry($data) {
        $res = inquiry::create($data);

        $hallN = hall::findOrFail($data['hall_id']);
        // توكنات الأونر
        $ownerTokens = DeviceToken::where('user_id', $hallN->owner_id)->pluck('device_token');

        // توكنات الموظفين
        $staffTokens = DeviceToken::whereIn('user_id', $hallN->employee()->pluck('user_id'))->pluck('device_token');

        // دمج الكل بمصفوفة وحدة
        $allTokens = $ownerTokens->merge($staffTokens);

        foreach ($allTokens as $token) {
            FirebaseNotificationService::sendNotification(
                $token,
                "New inquiry",
                "{$data['message']}"
            );
        }
        return $res;
    }

    public function getMyInquiries($userId, $hallId) {
        $inquiries = Inquiry::with(['responses' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])
            ->where('user_id', $userId)
            ->where('hall_id', $hallId)
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



    public function handleReview(Request $request , CommentAnalyzer $analyzer)
    {

        $comment = $request->comment;
        $result = $analyzer->analyze($comment);

        if($result === 'Bad'){
            return response()->json(['message' => 'Comment was rejected due to using Bad Words'], 403);
        }
        $review = Review::create([
            'user_id' => auth()->id(),
            'hall_id' => $request->hall_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'sentiment' => $result,
        ]);

        return response()->json(['message' => 'Your review was successfully stored',
            'review' => $review,
            ]);
    }



    public function getMyBookings() {
        $id = Auth::id();
        $bookings = Booking::where('user_id', $id)->get();
        return $bookings;
    }

    public function getABooking($id) {
        $bookings = Booking::where('id', $id)->get();
        return $bookings;
    }

    public function getHallsSortedByLocationSimilarity()
    {
        $id = Auth::id();
        $clientLoc = User::where('id' , $id)->value('location');
        if (!$id || !$clientLoc) {
            return response()->json(['error' => 'User location not set.'], 400);
        }
        $halls = hall::all();
        foreach ($halls as $hall) {
            similar_text($clientLoc, $hall->location, $percent);
            $hall->similarity = $percent;
        }

        // ترتيب تنازلي حسب نسبة التشابه
        return $halls->sortByDesc('similarity')->values();
    }

    public function costumeSearch(array $filters) {
        $query = hall::query();

        if(!empty($filters['name'])) {
            $query->where('name', 'like','%'.$filters['name'].'%');
        }

        if(!empty($filters['capacity'])) {
            $query->where('capacity', '>=',$filters['capacity']);
        }

        if(!empty($filters['location'])) {
            $query->where('location', 'like','%'.$filters['location'].'%');
        }

        return $query->get();
    }

    public function storeComplaint(Request $request , $hall_id) {

        $analyzer = new CommentAnalyzer();
        $res = $analyzer->analyze($request->complaint);
        if($res === 'Bad'){
            return response()->json(['message' => 'Complaint was rejected due to using Bad Words'], 403);
        }
        $complaint = Complaint::create([
            'user_id' => auth()->id(),
            'hall_id' => $hall_id,
            'complaint' => $request->complaint,
        ]);
        $user = User::find(auth()->id());
        $hall = hall::find($hall_id);
        $clientTokens = DeviceToken::where('user_id', 1)->pluck('device_token');

        $firebase = new FirebaseNotificationService();

        foreach ($clientTokens as $token) {
            $firebase->sendNotification(
                $token,
                "new complaint",
                "user {$user->name} sent a complaint on hall {$hall->name}"
            );
        }

        return response()->json(['message' => 'Your complaint was successfully stored and will be reviewed', 'review' => $complaint]);
    }

    public function getComplaint() {
        $comps = Complaint::where('user_id' , auth()->id())->with('hall')->get();

        return response()->json($comps);
    }

}
