<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Detail_booking;
use App\Models\hall;
use App\Models\Hall_img;
use App\Models\Office_service;
use App\Models\Office;
use App\Models\Sendanswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OfficeService
{
    public function addOffice(array $data)
    {
        $office = Office::create([

            'name' => $data['name'],
            'number' => $data['number'],
            'location' => $data['location'],

        ]);

        if (isset($data['photo'])) {
          //  $imagePath = $data['photo']->store('office' , 'public');
            $imageName = uniqid() . '_office_images_.' . $data['photo']->getClientOriginalExtension();
             $data['photo']->move(public_path(), $imageName);
            $office->photo = $imageName;
        }
        $office->save();
        return ['message'=>"the office added succesfuly",'service'=>$office];
}


public function showOffice(){
    $office = Office::select('id','name','photo')->get();
    $message="all office";
    return ['message'=>$message,'office'=>$office];
}

public function getOfficeDetailsWithServices($officeId)
{
    // الحصول على المكتب مع الخدمات
    $office = Office::with('services')->findOrFail($officeId);

     if (!$office) {
         return response()->json(['message' => 'Office not found'], 404);
 }
    $officeDetails = [
        'name' => $office->name,
        'location' => $office->location,
        'number' => $office->number,
        'officeId'=>$officeId,
        'services' => $office->services->map(function ($service) {
            return [
                'type_car' => $service->type_car,
                'car_image' => $service->car_image,
            ];
        }),
    ];

    return response()->json($officeDetails);
}


    public function addservice(array $data,$office_id)
    {
       $id= Office::where('id',$office_id)->exists();
       if($id){
        $officeSer = Office_service::create([
            'type_car' => $data['type_car'],
            'number_ofcar' => $data['number_ofcar'],
            'office_id'=>$office_id

        ]);

        if (isset($data['car_image'])) {
           // $imagePath = $data['car_image']->store('car_image' , 'public');
           $imageName = uniqid() . '_office_images_.' . $data['car_image']->getClientOriginalExtension();
           $data['car_image']->move(public_path(), $imageName);
            $officeSer->car_image = $imageName;
        }
        $officeSer->save();
        return ['message'=>"the service added succesfuly",'service'=>$officeSer];
       }
       else{
        return ['message'=>"the office not found"];
       }
        
}

public function showserviceoffice(){
    $services= Office_service::all();
$message="this is services to  office";
    return ['message'=>$message,'service'=>$services];

 }
public function addReqReservation(array $data,$service_id){
    $req =Detail_booking::create([
        'from' => $data['from'],
        'to' => $data['to'],
        'car_type' => $data['car_type'],
        'num_car' => $data['num_car'],
        'time' => $data['time'],
        'date_day' =>$data['date_day'] ,
        'description' => $data['description'],
        'user_id'=>Auth::id(),
        'office_service_id'=>$service_id

    ]);
    return ['message'=>"the request res added succesfuly",'service'=>$req];
}
public function showReqReservation(){
    $show=Detail_booking::join('users','users.id','Detail_bookings.user_id')
    ->select('users.id','users.name','users.number','users.photo','Detail_bookings.time')
    ->get();
    return $show;
}


public function get_detail($det_id) {
    $exist= Detail_booking::where('id',$det_id)->exists();
    if($exist){
    $det= Detail_booking::join('users', 'detail_bookings.user_id', 'users.id')
    -> select('detail_bookings.*', 'users.number', 'users.name','users.photo')
                        ->where('detail_bookings.id', $det_id)
                        ->get();

    return $det;
}
else{
    $message="the record not found";
    return $message;
}}
public function add_info_contact(array $data ,$officeId){

    $contact =Contact::create([
        'phone'=> $data['phone'],
        'description' => $data['description'],
        'office_id'=>$officeId,
    ]);

    return ['message'=>"the contact info added succesfuly",'contact'=>$contact];


}

public function send_answer($detail_id,$user_id , $officeId,array $data){
    $exist= Detail_booking::where('id',$detail_id)->where('user_id',$user_id)->exists();
    if($exist){
        $send=Sendanswer::create([
            'answer'=>$data['answer'],
            'user_id'=>$user_id,
            'detail_id'=>$detail_id,
            'office_id'=>$officeId
        ]);
    }
    return ['message'=>"the message send succesfuly",'answer'=>$send];

}
//get answer to user reservation
public function getAnswer($user_id){
    $exist= Sendanswer::where('user_id',$user_id)->exists();
    if($exist){
        $office=Office::with('answer')->first();
        if (!$office) {
            return response()->json(['message' => 'Office not found'], 404);
    }
    if (!$office->answer) {
        return response()->json(['message' => 'No answer found for this office'], 404);
    }
       $answerForreservation = [
           'name'=> $office->name,
          'photo'=>$office->photo,
           'answer' => $office->answer->answer    
           
       ];
   
       return response()->json($answerForreservation);

}
return response()->json(['message' => 'you are not reservation in this office'], 404);

}
}