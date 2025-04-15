<?php

namespace App\Services;

use App\Models\hall;
use App\Models\inquiry;
use App\Models\inquiryResponse;
use App\Models\staff_requests;

class AssistantService
{
    public function responseToInquiry($data) {
        return inquiryResponse::create($data);
    }

    public function requestStaff($data) {
        return staff_requests::create($data);
    }

}
