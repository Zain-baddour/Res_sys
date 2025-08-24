<?php

namespace App\Http\Controllers;

use App\Models\hall_employee;
use Illuminate\Http\Request;
use App\Services\ClientService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\CommentAnalyzer;


class ClientController extends Controller
{
    protected $clientService;
    protected $analyzer;

    public function __construct(ClientService $clientService, CommentAnalyzer $analyzer) {
        $this->clientService = $clientService;
        $this->analyzer = $analyzer;
    }

    public function store(Request $request) {
        $data = $request->validate([
            'hall_id' => 'required|exists:halls,id',
            'message' => 'required|string',
        ]);
        $data['user_id'] = auth()->id();
        $inquiry = $this->clientService->createInquiry($data);
        return response()->json($inquiry, 201);
    }

    public function myInquiries($hallId = null, $userId = null) {
        $user = auth()->user();

        if($user->hasRole('client')) {
            $userId = $user->id;
            if (!$hallId) {
                return response()->json(['error' => 'Hall id is required for clients'],400);
            }
        }
        elseif ($user->hasRole('assistant')) {
            $hallId = hall_employee::where('user_id', $user->id)->value('hall_id');
            if (!$hallId) {
                return response()->json(['error' => 'you are not assigned to any hall'],403);
            }
        }

        else {
            return response()->json(['error' => 'Unauthorized'],403);
        }

        return response()->json($this->clientService->getMyInquiries($userId, $hallId));
    }

    public function storeReview(Request $request ,CommentAnalyzer $analyzer)
    {
        $request->validate([
            'hall_id' => 'required|exists:halls,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        return $this->clientService->handleReview($request , $analyzer);
    }

    public function getMyBook() {
        $books = $this->clientService->getMyBookings();
        return response()->json($books->load(['hall','payment','services','songs']));
    }
    public function getABook($id) {
        $book = $this->clientService->getABooking($id);
        return response()->json($book->load(['hall','payment','services','songs']));
    }

    public function nearbyHalls()
    {

        $halls = $this->clientService->getHallsSortedByLocationSimilarity();

        return response()->json([
            'halls' => $halls
        ]);
    }

    public function searchHalls (Request $request) {
        $filters = $request->only(['name','capacity','location']);
        $halls = $this->clientService->costumeSearch($filters);
        return response()->json([
            'halls' => $halls,
        ]);
    }

    public function storeComplaint(Request $request , $hall_id) {
        $request->validate([
            'complaint' => 'required|string|max:10000'
        ]);
        return $this->clientService->storeComplaint($request , $hall_id);

    }

    public function getComplaint() {
        return $this->clientService->getComplaint();
    }
}
