<?php

namespace App\Services;

use App\Models\DetailsHall;
use App\Models\hall;
use App\Models\hall_employee;
use App\Models\Hall_img;
use App\Models\Image_hal;
use App\Models\inquiry;
use App\Models\Offer;
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
            return hall::with('images')->where('status', 'approved')->get()->map(function ($hall){
                return [
                    'id' => $hall->id,
                    'name' => $hall->name,
                    'owner_id' => $hall->owner_id,
                    'capacity' => $hall->capacity,
                    'location' => $hall->location,
                    'contact' => $hall->contact,
                    'type' => $hall->type,
                    'events' => $hall->events,
                    'hall_image' => $hall->hall_image,
                    'images' => $hall->images->map(function (Hall_img $image) {
                        return $image->image_path;
                    }),
                ];
            });
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
            return hall::with('images')->where('id', $id)->get()->map(function ($hall){
                return [
                    'id' => $hall->id,
                    'name' => $hall->name,
                    'owner_id' => $hall->owner_id,
                    'capacity' => $hall->capacity,
                    'location' => $hall->location,
                    'contact' => $hall->contact,
                    'type' => $hall->type,
                    'events' => $hall->events,
                    'hall_image' => $hall->hall_image,
                    'images' => $hall->images->map(function (Hall_img $image) {
                        return $image->image_path;
                    }),
                ];
            });
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
                 //   if (isset($data['description']) && !empty($data['description'])) {
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
                'removable'=>$data['removable'],
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



    public function updatepolices($id,array $data)
    {
        $police = Policies::findOrFail($id);
       // if (isset($data['description']) && !empty($data['description'])) {
        if($police){
        $police->update(
           $data);
        return $police;
    }
//     else {
//         $message = "The hall does not exist.";
//         return $message;
// }
        // else{
        //     $message = "Description is empty or missing in the data array.";
        //     return $message;
        // }

   }
    public function getpolicesById($id)
    {

        return Policies::findOrFail($id);

    }

    public function add_detail(array $data)
    {
        if (Auth::user()->hasRole('assistant')){

            $detail = DetailsHall::create($data);

            return $detail;
        }else
        { $message="you are not employee in the hall";
            return $message;

        }
    }
    public function getdetailById($id)
    {

        return DetailsHall::findOrFail($id);

    }

    public function updatedetail(array $data,$id)
    {

        $detail = DetailsHall::findOrFail($id);

        $detail->update([
            'card_price'=>$data['card_price'],
            'type_hall' =>$data['type_hall'],
            'res_price' =>$data['res_price']
        ]);

        return $detail;
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

            return $service;
                }
        }else
        { $message="you are not employee in the hall";
            return $message;
        }
    }

    public function updateservice($id,array $data)
    {
        $service = Servicetohall::findOrFail($id);
        $service->update($data);
        return $service;
    }



}
