<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OwnerService;

class OwnerController extends Controller
{
    protected $ownerService;

    public function __construct(OwnerService $ownerservice)
    {
        $this->ownerService = $ownerservice;
    }

    public function showMyHall(){
        $hall = $this->ownerService->myHallSer();
        return response()->json($hall);
    }

    public function getStaffReqs(){
        $requests = $this->ownerService->getStaffRequests();
        return response()->json($requests);
    }

    public function updateStaffReqStatus(Request $request, $id) {
        $request->validate([
            'status' => 'sometimes|string|in:approved,rejected'
        ]);
        $staffReq = $this->ownerService->approveOrRejectStaff($id, $request->status);
        return response()->json($staffReq);
    }

}
