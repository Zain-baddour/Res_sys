<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Complaint;
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
        return inquiry::create($data);
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

    protected $badWords = [
        // كلمات نابية بالعربية
        'كلب','حمار','حقير','تافه','غبي','سخيف','خرا','قذر','نجس','وسخ','تفو','تباً',
        'يلعن','حيوان','ابن كلب','ابن حرام','منيك','مخنث','شاذ','زامل','قحبة','عاهرة',
        'شرموطة','نيك','عرص','كس','طيز','زب','لوطي','متناك','مقرف','منيوك','أبوك',
        'أمك','انقلع','انطم','انكتم','اقطع','كسمك','الطم','عوي',

        // English bad words
        'fuck','shit','bitch','asshole','dick','pussy','fag','faggot','retard','crap',
        'bastard','slut','whore','cunt','damn','jerk','moron','suck','douche','prick',
        'balls','tits','boobs','nuts','shithead','arse','fucking','freak','homo',
        'kiss my ass','blowjob','nigger','nigga'
    ];

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

    protected function isCommentClean(string $comment): bool
    {
        foreach ($this->badWords as $badWord) {
            if (stripos($comment, $badWord) !== false) {
                return false;
            }
        }

        return true;
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

    public function storeComplaint(Request $request , $hall_id) {

        $complaint = Complaint::create([
            'user_id' => auth()->id(),
            'hall_id' => $hall_id,
            'complaint' => $request->complaint,
        ]);

        return response()->json(['message' => 'Your complaint was successfully stored and will be reviewed', 'review' => $complaint]);
    }

    public function getComplaint() {
        $comps = Complaint::where('user_id' , auth()->id())->with('hall')->get();

        return response()->json($comps);
    }

}
