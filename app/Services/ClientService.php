<?php

namespace App\Services;

use App\Models\hall;
use App\Models\inquiry;

class ClientService
{
    public function createInquiry($data) {
        return inquiry::create($data);
    }

    public function getMyInquiries($userId) {
        return inquiry::where('user_id', $userId)->with('responses')->get();
    }

}
