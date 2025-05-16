<?php

namespace App\Services;

use App\Models\DetailsHall;
use App\Models\hall;
use App\Models\hall_employee;
use App\Models\Hall_img;
use App\Models\Detail_img;
use App\Models\Image_hal;
use App\Models\inquiry;
use App\Models\Loungetiming;
use App\Models\Offer;
use App\Models\Paymentway;
use App\Models\Policies;
use App\Models\Servicetohall;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HallService
{
    /**
     * Get all halls.
     */
    public function getAll()
    {
        try {
            return hall::with('images')
                ->withAvg('reviews', 'rating')
                ->where('status', 'approved')
                ->get();
//                ->map(function ($hall){
//                return [
//                    'id' => $hall->id,
//                    'name' => $hall->name,
//                    'owner_id' => $hall->owner_id,
//                    'capacity' => $hall->capacity,
//                    'location' => $hall->location,
//                    'contact' => $hall->contact,
//                    'type' => $hall->type,
//                    'events' => $hall->events,
//                    'hall_image' => $hall->hall_image,
//                    'images' => $hall->images->map(function (Hall_img $image) {
//                        return $image->image_path;
//                    }),
//                    'average_rating' => round($hall->reviews->avg('rating'), 1),
//                ];
//            });
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }

    }

    /**
     * Get a single hall by ID.
     */
    public function getById($id)
    {
        try {
            return hall::with(['images', 'owner:id,photo'])
                ->withAvg('reviews', 'rating')
                ->where('id', $id)
                ->get();
//                ->map(function ($hall){
//                return [
//                    'id' => $hall->id,
//                    'name' => $hall->name,
//                    'owner_id' => $hall->owner_id,
//                    'capacity' => $hall->capacity,
//                    'location' => $hall->location,
//                    'contact' => $hall->contact,
//                    'type' => $hall->type,
//                    'events' => $hall->events,
//                    'hall_image' => $hall->hall_image,
//                    'images' => $hall->images->map(function (Hall_img $image) {
//                        return $image->image_path;
//                    }),
//                ];
//            });
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }

    }

    /**
     * Create a new hall.
     */
    public function create(array $data)
    {
        $hall = Hall::create([
            'name' => $data['name'],
            'owner_id' => Auth::id(),
            'capacity' => $data['capacity'],
            'location' => $data['location'],
            'contact' => $data['contact'],
            'type' => $data['type'],
            'events' => $data['events'],
        ]);

        if (isset($data['hall_image'])) {
            $photoName = uniqid() . '_hall_image_.' . $data['hall_image']->getClientOriginalExtension();
            $data['hall_image']->move(public_path(), $photoName);
            $hall->hall_image = $photoName;
        }

        if (isset($data['images'])) {
            foreach ($data['images'] as $image) {
                $path = uniqid() . '_images_.' . $image->getClientOriginalExtension();
                $image->move(public_path(), $path);
                Hall_img::create([
                   'hall_id' => $hall->id,
                   'image_path' => $path,
                ]);
            }
        }

        $hall->save();
        return $hall;

    }

    /**
     * Update an existing hall.
     */
    public function update($id, array $data)
    {
        $hall = Hall::findOrFail($id);
        $hall->update($data);
        return $hall;
    }

    /**
     * Delete a hall.
     */
    public function delete($id)
    {
        $hall = Hall::findOrFail($id);
        $hall->delete();
        return true;
    }

    public function getHallReviews ($hall_id) {
        $hall = hall::with(['reviews' => function($query) {
            $query->latest()->with('user');
        }])->findOrFail($hall_id);

        return response()->json([
            'reviews' => $hall->reviews
        ]);
    }

    public function getHallImages($hallId) {

        return Hall_img::where('hall_id', $hallId)->pluck('image_path');
    }

    public function getInquiriesByHall($hallId) {
        return inquiry::where('hall_id', $hallId)->with('responses')->get();
    }

    public function getHallEmployee($hallId) {
        $hall = hall::findOrFail($hallId);
        return $hall->employee()->with('user')->get();
    }

    public function delHallEmployee($employeeId) {
        $employee = hall_employee::findOrFail($employeeId);
        return $employee->delete();
    }


    //**************************************************
    // ZainHassan *************


    public function addpolices(array $data,$hall_id)
    {
        if (Auth::user()->hasRole('assistant')){
            $exist= hall::where('id',$hall_id)->exists();
                if($exist){
            $polices= Policies::create([
            'description'=>$data['description'],
                'hall_id'=>$hall_id
            ]);
             return $polices;
        } else {
            $message = "The hall does not exist.";
            return $message;
    }}
        else{
            $message="you are not employee in the hall";
            return $message;
        }
    }
    public function addoffer(array $data,$hall_id)
    {

        if (Auth::user()->hasRole('assistant')){
            $exist= hall::where('id',$hall_id)->exists();
            if($exist){
            return Offer::create(
                ['period_offer'=>$data['period_offer'],
                'start_offer'=>$data['start_offer'],

                'offer_val'=>$data['offer_val'],
                'hall_id'=>$hall_id
            ]);
        }
        else {
            $message = "The hall does not exist.";
            return $message;}
        }
        else {
            $message="you are not employee in the hall";
            return $message;
        }
    }
    public function updateoffer($id,array $data)
    {
        $offer = Offer::findOrFail($id);

        if($offer){
        $offer->update(
           $data);
        return $offer;
    }

    else {
        $message = "The offer does not exist.";
        return $message;
}

    }
    public function showoffer($hall_id){
        $offers= Offer::where('hall_id',$hall_id)->get();
 $message="this is offers to hall";
        return ['message'=>$message,'service'=>$offers];

     }



    public function updatepolices($id,array $data)
    {
        $police = Policies::findOrFail($id);

        if($police){
        $police->update(
           $data);
        return $police;
    }

    else {
        $message = "The polices does not exist.";
        return $message;
}

   }

   public function showspolices($hall_id){
    $polices= Policies::where('hall_id',$hall_id)->get();
$message="this is polices to hall";
    return ['message'=>$message,'polices'=>$polices];

 }


    public function add_detail(array $data,$hall_id)
    {
        if (Auth::user()->hasRole('assistant')){
            $exist= hall::where('id',$hall_id)->exists();
            if($exist){
            $detail = DetailsHall::create( [
                'type_hall'=>$data['type_hall'],
                'card_price'=>$data['card_price'],
                'res_price'=>$data['res_price'],
                'num_person'=>$data['num_person'],
                'location'=>$data['location'],
                'number'=>$data['number'],
                'hall_id'=>$hall_id
            ]);
            if (isset($data['images'])) {
                foreach ($data['images'] as $image) {
                    $path = uniqid() . '_images_.' . $image->getClientOriginalExtension();
                    $image->store('detail_image' , 'public');;
                    Detail_img::create([
                       'detail_id' => $detail->id,
                       'image_path' => $path,
                    ]);
                }}
                if (isset($data['video'])) {
                    $video = $data['video'];
                    $videoPath = uniqid() . '_video_.' . $video->getClientOriginalExtension();

                    // قم بإجراء التحقق من الفيديو هنا
                    if ($video->isValid()) {
                        $video->storeAs('detail_videos', $videoPath, 'public');
                        $detail->video_path = $videoPath;

                    }}
                $detail->save();
            return $detail;
        }
        else {
            $message = "The hall does not exist.";
            return $message;}
        }
        else{
            $message="you are not employee in the hall";
            return $message;
        }
    }

    public function showdetail($hall_id){
        $details= DetailsHall::where('hall_id',$hall_id)->value('id');
        if($details){
            $det= DetailsHall::join('detail_imgs', 'detail_imgs.detail_id', 'details_halls.id')
            -> select('details_halls.*', 'detail_imgs.image_path')
                                ->where('detail_imgs.detail_id', $details)
                                ->get();

            return $det;
      }

    }
    public function updatedetail(array $data,$id)
    {

        $detail = DetailsHall::findOrFail($id);
       // $hall_id= DetailsHall::select('details_halls.hall_id') ->where('details_halls.id', $id)->get();
if($detail){
        $detail->update([
            'card_price'=>$data['card_price'],
            'type_hall' =>$data['type_hall'],
            'res_price' =>$data['res_price'],
            'num_person'=>$data['num_person'],
            'location'=>$data['location'],
            'number'=>$data['number'],
        ]);
        if (isset($data['images'])) {
            foreach ($data['images'] as $image) {
                $path = uniqid() . '_images_.' . $image->getClientOriginalExtension();
                $image->store('detail_image' , 'public');
                Detail_img::create([
                    'detail_id' => $detail->id,
                    'image_path' => $path,
                 ]);
            }}if (isset($data['video'])) {
                $video = $data['video'];
                $videoPath = uniqid() . '_video_.' . $video->getClientOriginalExtension();

                // قم بإجراء التحقق من الفيديو هنا
                if ($video->isValid()) {
                    $video->storeAs('detail_videos', $videoPath, 'public');
                    $detail->video_path = $videoPath;

                }}
            $detail->save();
        return $detail;
    }else{
        $message = "The detail  not found.";
        return $message;

    }
    }
    public function add_service(array $data,$hall_id)
    {
            if (Auth::user()->hasRole('assistant')){
                $exist= hall::where('id',$hall_id)->exists();
                if($exist){
            $service = Servicetohall::create(
               ['name'=>$data['name'],
            'price'=>$data['price'],
            'description'=>$data['description'],
            'hall_id'=>$hall_id
             ] );

 if (isset($data['images'])) {
                foreach ($data['images'] as $image) {
                    $path = uniqid() . '_images_.' . $image->getClientOriginalExtension();
                    $image->store('servic_image' , 'public');;
                    Hall_img::create([
                       'hall_id' => $hall_id,
                       'image_path' => $path,
                    ]);
                }}

            if (isset($data['video'])) {
                $video = $data['video'];
                $videoPath = uniqid() . '_video_.' . $video->getClientOriginalExtension();

                // قم بإجراء التحقق من الفيديو هنا
                if ($video->isValid()) {
                    $video->storeAs('service_videos', $videoPath, 'public');
                    $service->video_path = $videoPath;

                }}
                $service->save();
                return $service;
        }}else
        { $message="you are not employee in the hall";
            return $message;
        }
    }

    public function updateservice(array $data,$id)
    {
        $service = Servicetohall::findOrFail($id);
        if($service){
            $service->update(['name'=>$data['name'],
            'price'=>$data['price'],
            'description'=>$data['description']
        ]);
            return $service;
        }
        else{
            $message = "The service  not found.";
            return $message;
        }

    }
    public function showservice($hall_id){
       $services= Servicetohall::where('hall_id',$hall_id)->get();
$message="this is services to hall";
       return ['message'=>$message,'service'=>$services];

    }

    public function add_time(array $data,$hall_id){
        if (Auth::user()->hasRole('assistant')){
            $exist= hall::where('id',$hall_id)->exists();
            if($exist){
        $time = Loungetiming::create(
           ['type'=>$data['type'],
        'from'=>$data['from'],
        'to'=>$data['to'],
        'hall_id'=>$hall_id
         ] );

        return $time;
            }
    }else  {
         $message="you are not employee in the hall";
        return $message;
    }

}

public function updatetime(array $data,$id)
{
    $time = Loungetiming::findOrFail($id);
    if($time){
        $time->update([
            'type'=>$data['type'],
        'from'=>$data['from'],
        'to'=>$data['to'],
    ]);
        return $time;
    }
    else{
        $message = "The time off hall  not found.";
        return $message;
    }   }

    public function showtime($hall_id){
        $times= Loungetiming::where('hall_id',$hall_id)->get();
 $message="the time of hall";
        return ['message'=>$message,'service'=>$times];

     }
     public function addpay(array $data,$hall_id)
     {
         if (Auth::user()->hasRole('assistant')){
             $exist= hall::where('id',$hall_id)->exists();
                 if($exist){

             $pay= Paymentway::create([
             'type'=>$data['type'],
                 'hall_id'=>$hall_id
             ]);
              return $pay;
         } else {
             $message = "The hall does not exist.";
             return $message;
     }}
         else{
             $message="you are not employee in the hall";
             return $message;
         }
     }

     public function updatepay(array $data,$id){
    $pay= Paymentway::findOrFail($id);
    if($pay){
        $pay->update([
            'type'=>$data['type'],
    ]);
        return $pay;
    }
    else{
        $message = "The payment way off hall  not found.";
        return $message;
    }   }

}
