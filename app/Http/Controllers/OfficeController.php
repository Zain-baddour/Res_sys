<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OfficeService;
use Symfony\Contracts\Service\Attribute\Required;

class OfficeController extends Controller
{

    protected $officeService;

    public function __construct(OfficeService $officeService)
    {
        $this->officeService = $officeService;
    }

    public function addoffice(Request $request)
    {
        $data = $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'number' => 'required|string|min:9',
        ]);

        $office = $this->officeService->addOffice($data);
        return response()->json($office);
    }


    public function showoffice(){
        $office = $this->officeService->showOffice();
        return response()->json($office);
    }

//get detail the office
    public function showDetailOffice($office_id){
        $detail=$this->officeService->getOfficeDetailsWithServices($office_id);
        return response()->json($detail);
    }

    

    public function addserv(Request $request,$office_id)
    {
        $data = $request->validate([
            'car_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type_car' => 'required|string|max:255',
           // 'num_ofcar' => 'required|integer|min:1',
        ]);

        $office = $this->officeService->addservice($data,$office_id);
        return response()->json($office);
    }

    public function showservice()
    {
        $service = $this->officeService->showserviceoffice();
        return response()->json($service);
    }

    public function addReqReservation(Request $request, $service_id)
    {
        $data = $request->validate([
            'from' => 'required|string|max:255',
            'to' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'time' => 'required|date_format:H:i',
            'date_day'=>'Required|date',
            'car_type' => 'nullable|string|max:255|',
            'num_car' => 'required|integer|min:1',
            // 'user_id'=>'required',
            // 'office_id'=>'required'
        ]);

        $reservation = $this->officeService->addReqReservation($data, $service_id);
        return response()->json($reservation);
    }

    public function showReqReservation()
    {
        $show = $this->officeService->showReqReservation();
        return response()->json($show);
    }

    public function get_detail($det_id)
    {
        $detail = $this->officeService->get_detail($det_id);
        return response()->json($detail);
    }

    public function add_info_contact(Request $request , $officeId)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'phone' => 'required|numeric|min:8'
        ]);
        $contact = $this->officeService->add_info_contact($data , $officeId);
        return response()->json($contact);
    }


    
    public function send_answer($detail_id, $user_id ,$officeId , Request $request)
    {
        $data = $request->validate([
            'answer' => 'required|string|max:255',

        ]);
        $answer = $this->officeService->send_answer($detail_id, $user_id, $officeId , $data);
        return response()->json($answer);
    }



    public function getAnswer($user_id){
        $answer = $this->officeService->getAnswer($user_id);
        return response()->json($answer);

    }
}
