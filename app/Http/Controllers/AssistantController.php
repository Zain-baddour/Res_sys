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

    public function getChat () {
        $response = $this->assistantService->getChats();
        return response()->json($response, 201);
    }

    public function uploadEventImages(Request $request) {
        $data = $request->validate([
            'event_type' => 'nullable|string|in:joys,sorrows',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $response = $this->assistantService->uploadEventImages($data);
        return response()->json($response , 200);
    }

    public function uploadEventVideos(Request $request) {
        $data = $request->validate([
            'event_type' => 'nullable|string|in:joys,sorrows',
            'video.*' => 'nullable|mimetypes:video/mp4,video/avi,video/mpg,video/mov,video/wmv|max:20480',
        ]);

        $response = $this->assistantService->uploadEventVideos($data);
        return response()->json($response , 200);
    }

}
