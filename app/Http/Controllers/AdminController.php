<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdmineService;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdmineService $admineService){
        $this->adminService = $admineService;
    }

    public function getPendingHalls()
    {
        $halls = $this->adminService->getPendingHalls();
        return response()->json($halls);
    }

    public function updateHallStatus(Request $request , $id)
    {
        $request->validate([
            'status' => 'sometimes|string|in:approved,rejected',
        ]);

        $hall = $this->adminService->updateHallStatus($id, $request->status);

        return response()->json($hall);
    }

}
