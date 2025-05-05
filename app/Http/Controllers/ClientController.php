<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClientService;


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

    public function myInquiries($hallId) {
        $userId = auth()->id();
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
}
