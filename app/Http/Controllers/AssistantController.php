<?php

namespace App\Http\Controllers;

use App\Services\AssistantService;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    protected $assistantService;

    public function __construct(AssistantService $assistantService)
    {
        $this->assistantService = $assistantService;
    }

    public function responseToInquiry(Request $request) {
        $data = $request->validate([
            'inquiry_id' => 'required|exists:inquiries,id',
            'response' => 'required|string',
        ]);
        $data['user_id'] = auth()->id();
        $response = $this->assistantService->responseToInquiry($data);
        return response()->json($response, 201);
    }

    public function requestStaff($id) {
        $data['hall_id'] = $id;
        $data['user_id'] = auth()->id();
        $response = $this->assistantService->requestStaff($data);
        return response()->json($response, 201);
    }

}
