<?php

namespace App\Http\Controllers;

use App\Models\hall_employee;
use Illuminate\Http\Request;
use App\Services\ClientService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService) {
        $this->clientService = $clientService;
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

    public function storeReview(Request $request)
    {
        $request->validate([
            'hall_id' => 'required|exists:halls,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        return $this->clientService->handleReview($request);
    }

    public function getMyBook() {
        $books = $this->clientService->getMyBookings();
        return response()->json($books->load(['hall','payment','services','songs']));
    }
}
