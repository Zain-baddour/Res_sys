<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Detail_booking;
use App\Models\hall;
use App\Models\Hall_img;
use App\Models\Office;
use App\Models\Sendanswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OfficeService
{
    public function addservice(array $data)
    {
        $office = Office::create([
           
            'num_ofcar' => $data['num_ofcar'],
            'type_car' => $data['type_car'],
          
        ]);

        if (isset($data['car_image'])) {
            $imagePath = $data['car_image']->store('car_image' , 'public');
            $office->car_image = $imagePath;
        }
        $office->save();
        return ['message'=>"the service added succesfuly",'service'=>$office];
}
public function addReqReservation(array $data,$office_id){
    $req =Detail_booking::create([
           
        'from' => $data['from'],
        'to' => $data['to'],
        'car_type' => $data['car_type'],
        'num_car' => $data['num_car'],
        'date' => $data['date'],
        'description' => $data['description'],
        'user_id'=>Auth::id(),
        'office_id'=>$office_id
      
    ]);
    return ['message'=>"the request res added succesfuly",'service'=>$req];
}
public function showReqReservation(){
    $show=Detail_booking::join('users','users.id','Detail_bookings.user_id')
    ->select('users.id','users.name','users.number','Detail_bookings.date')
    ->get();
    return $show;
}


public function get_detail($det_id) {
    $exist= Detail_booking::where('id',$det_id)->exists();
    if($exist){
    $det= Detail_booking::join('users', 'detail_bookings.user_id', 'users.id')
    -> select('detail_bookings.*', 'users.number', 'users.name')
                        ->where('detail_bookings.id', $det_id)
                        ->get();

    return $det;
}
else{
    $message="the record not found";
    return $message;
}}
public function add_info_contact(array $data){

    $contact =Contact::create([
        'phone'=> $data['phone'],
        'description' => $data['description'],
        'office_id'=>Auth::id(),
    ]);

    return ['message'=>"the contact info added succesfuly",'contact'=>$contact];


}

public function send_answer($detail_id,$user_id,array $data){
    $exist= Detail_booking::where('id',$detail_id)->where('user_id',$user_id)->exists();
    if($exist){
        $send=Sendanswer::create([
            'answer'=>$data['answer'],
            'user_id'=>$user_id,
            'detail_id'=>$detail_id,
            'office_id'=>Auth::id()
        ]);
       
    }
    return ['message'=>"the message send succesfuly",'contact'=>$send];

}

}