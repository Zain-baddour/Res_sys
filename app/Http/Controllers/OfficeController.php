<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OfficeService;


class OfficeController extends Controller
{
    
    protected $officeService;

    public function __construct(OfficeService $officeService){
        $this->officeService = $officeService;
    }
    public function addserv(Request $request)
    {
        $data = $request->validate([
            'car_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type_car' => 'required|string|max:255',
            'num_ofcar' => 'required|integer|min:1',]);

    $office = $this->officeService->addservice($data);
    return response()->json($office);
}
public function addReqReservation(Request $request,$office_id)
    {
        $data = $request->validate([
            'from' => 'required|string|max:255',
            'to' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'date'=>'required|date_format:H:i',
            'car_type' => 'nullable|string|max:255|',
           'num_car'=>'required|integer|min:1',
            // 'user_id'=>'required',
            // 'office_id'=>'required'
        ]);

    $reservation = $this->officeService->addReqReservation($data,$office_id);
    return response()->json($reservation);
}
public function showReqReservation(){
    $show = $this->officeService->showReqReservation();
    return response()->json($show);
}

public function get_detail($det_id){
  $detail= $this->officeService->get_detail($det_id);
    return response()->json($detail);
}

public function add_info_contact(Request $request)
{ $data = $request->validate([
    'description' => 'required|string|max:255',
    'phone' => 'required|numeric|min:8'
]);
    $contact=$this->officeService->add_info_contact($data);
    return response()->json($contact);
}
public function send_answer($detail_id,$user_id,Request $request){
    $data = $request->validate([
        'answer' => 'required|string|max:255',
        
    ]);
        $answer=$this->officeService->send_answer($detail_id,$user_id,$data);
        return response()->json($answer);
}
}