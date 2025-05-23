<?php

namespace App\Services;

use App\Models\DetailsHall;
use App\Models\hall;
use App\Models\hall_employee;
use App\Models\Hall_img;
use App\Models\Detail_img;
use App\Models\hallEventImages;
use App\Models\hallEventVideos;
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
            return hall::with(['images','video'])
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

    public function getHallReviews($hall_id)
    {
        $hall = hall::with(['reviews' => function ($query) {
            $query->latest()->with('user');
        }])->findOrFail($hall_id);

        return response()->json([
            'reviews' => $hall->reviews
        ]);
    }

    public function getHallImages($hallId)
    {

        return Hall_img::where('hall_id', $hallId)->pluck('image_path');
    }

    public function getInquiriesByHall($hallId)
    {
        return inquiry::where('hall_id', $hallId)->with('responses')->get();
    }

    public function getHallEmployee($hallId)
    {
        $hall = hall::findOrFail($hallId);
        return $hall->employee()->with('user')->get();
    }

    public function delHallEmployee($employeeId)
    {
        $employee = hall_employee::findOrFail($employeeId);
        return $employee->delete();
    }

    public function getEventImages($hallId) {
        return hallEventImages::where('hall_id', $hallId)->get();
    }

    public function getEventVideos($hallId) {
        return hallEventVideos::where('hall_id', $hallId)->get();
    }

    //**************************************************
    // ZainHassan *************


    public function addpolices(array $data, $hall_id)
    {
        if (Auth::user()->hasRole('assistant')) {
            $exist = hall::where('id', $hall_id)->exists();
            if ($exist) {
                $polices = Policies::create([
                    'description' => $data['description'],
                    'hall_id' => $hall_id
                ]);
                return $polices;
            } else {
                $message = "The hall does not exist.";
                return $message;
            }
        } else {
            $message = "you are not employee in the hall";
            return $message;
        }
    }

    public function addoffer(array $data, $hall_id)
    {

        if (Auth::user()->hasRole('assistant')) {
            $exist = hall::where('id', $hall_id)->exists();
            if ($exist) {
                return Offer::create(
                    ['period_offer' => $data['period_offer'],
                        'start_offer' => $data['start_offer'],

                        'offer_val' => $data['offer_val'],
                        'hall_id' => $hall_id
                    ]);
            } else {
                $message = "The hall does not exist.";
                return $message;
            }
        } else {
            $message = "you are not employee in the hall";
            return $message;
        }
    }

    public function updateoffer($id, array $data)
    {
        $offer = Offer::findOrFail($id);

        if ($offer) {
            $offer->update(
                $data);
            return $offer;
        } else {
            $message = "The offer does not exist.";
            return $message;
        }

    }

    public function showoffer($hall_id)
    {
        $offers = Offer::where('hall_id', $hall_id)->get();
        $message = "this is offers to hall";
        return ['message' => $message, 'service' => $offers];

    }


    public function updatepolices($id, array $data)
    {
        $police = Policies::findOrFail($id);

        if ($police) {
            $police->update(
                $data);
            return $police;
        } else {
            $message = "The polices does not exist.";
            return $message;
        }

    }

    public function showspolices($hall_id)
    {
        $polices = Policies::where('hall_id', $hall_id)->get();
        $message = "this is polices to hall";
        return ['message' => $message, 'polices' => $polices];

    }


    public function add_detail(array $data, $hall_id)
    {
        if (Auth::user()->hasRole('assistant')) {
            $hall = Hall::findOrFail($hall_id);
            if ($hall) {
                $hall->update([
                    'location' => $data['location'],
                    'capacity' => $data['capacity'],
                    'contact' => $data['contact'],
                    'type' => $data['type'],

                ]);
                if (isset($data['images'])) {
                    foreach ($data['images'] as $image) {
                        $imageName = uniqid() . '_hall_images_.' . $image->getClientOriginalExtension();
                        $path = $image->move(public_path(), $imageName);
                        $hall->images()->create(['image_path' => $imageName]);

                    }
                }
                if (isset($data['video'])) {
                    foreach ($data['video'] as $video) {
                        $videoName = uniqid() . '_hall_video_.' . $video->getClientOriginalExtension();
                        $path = $video->move(public_path(), $videoName);
                        $hall->video()->create(['video_path' => $videoName]);

                    }
                }
                $hall->save();
                return $hall->load(['images','video']);
            } else {
                $message = "The hall does not exist.";
                return $message;
            }
        } else {
            $message = "you are not employee in the hall";
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

    public function add_service($data, $hallId)
    {
        $data['hall_id'] = $hallId;
        $service = Servicetohall::create($data);
        $service->save();

        if (isset($data['images'])) {
            foreach ($data['images'] as $image) {
                $imageName = uniqid() . '_service_images_.' . $image->getClientOriginalExtension();
                $path = $image->move(public_path(), $imageName);
                $service->images()->create(['image_path' => $imageName]);

            }
        }

        if (isset($data['video'])) {
            $video = $data['video'];
            $videoName = uniqid() . '_service_video_.' . $video->getClientOriginalExtension();
            $videoPath = $video->move(public_path(), $videoName);
            $service->video()->create(['video_path' => $videoName]);
        }


        return $service->load('images', 'video');
    }

    public function updateservice($data, $id)
    {
        $service = Servicetohall::findOrFail($id);
        if ($service) {
            $service->update($data);

            if (isset($data['images'])) {
                $service->images()->delete();
                foreach ($data['images'] as $image) {
                    $imageName = uniqid() . '_service_images_.' . $image->getClientOriginalExtension();
                    $path = $image->move(public_path(), $imageName);
                    $service->images()->create(['image_path' => $imageName]);

                }
            }

            if (isset($data['video'])) {
                $service->video()->delete();
                $video = $data['video'];
                $videoName = uniqid() . '_service_video_.' . $video->getClientOriginalExtension();
                $videoPath = $video->move(public_path(), $videoName);
                $service->video()->create(['video_path' => $videoName]);
            }

            $service->save();
            return $service->load('images', 'video');
        } else {
            $message = "The service  not found.";
            return $message;
        }

    }

//    public function showservice($hall_id)
//    {
//        $services = Servicetohall::where('hall_id', $hall_id)->with(['images', 'video'])->get();
//        $message = "this is services to hall";
//        return ['message' => $message, 'service' => $services];
//
//    }
    public function showservice($hall_id)
    {
        $hall = Hall::findOrFail($hall_id);

        $allServices = Servicetohall::where('hall_id', $hall_id)->with(['images', 'video'])->get();

        // قائمة الخدمات حسب الاسم
        $joys_names = ['buffet_service','hospitality_services','performance_service','car_service','decoration_service','photographer_service','protection_service',
            'promo_service']; // عدّل حسب خدمات الأفراح
        $sorrows_names = ['reader_service','condolence_photographer_service','condolence_hospitality_services']; // عدّل حسب خدمات العزاء

        $response = ['message' => 'this is services to hall'];

        if ($hall->type === 'joys') {
            $response['joys_services'] = $allServices;
        } elseif ($hall->type === 'sorrows') {
            $response['condolences_services'] = $allServices;
        } elseif ($hall->type === 'both') {
            $response['joys_services'] = $allServices->filter(function ($service) use ($joys_names) {
                return in_array($service->name, $joys_names);
            })->values();

            $response['condolences_services'] = $allServices->filter(function ($service) use ($sorrows_names) {
                return in_array($service->name, $sorrows_names);
            })->values();
        }

        return response()->json($response);
    }

    public function add_time(array $data, $hall_id)
    {
        if (Auth::user()->hasRole('assistant')) {
            $exist = hall::where('id', $hall_id)->exists();
            if ($exist) {
                $time = Loungetiming::create(
                    ['type' => $data['type'],
                        'from' => $data['from'],
                        'to' => $data['to'],
                        'hall_id' => $hall_id
                    ]);

                return $time;
            }
        } else {
            $message = "you are not employee in the hall";
            return $message;
        }

    }

    public function updatetime(array $data, $id)
    {
        $time = Loungetiming::findOrFail($id);
        if ($time) {
            $time->update([
                'type' => $data['type'],
                'from' => $data['from'],
                'to' => $data['to'],
            ]);
            return $time;
        } else {
            $message = "The time off hall  not found.";
            return $message;
        }
    }

    public function showtime($hall_id)
    {
        $times = Loungetiming::where('hall_id', $hall_id)->get();
        $message = "the time of hall";
        return ['message' => $message, 'service' => $times];

    }

    public function addpay(array $data, $hall_id)
    {
        if (Auth::user()->hasRole('assistant')) {
            $exist = hall::where('id', $hall_id)->exists();
            if ($exist) {

                $pay = Paymentway::create([
                    'type' => $data['type'],
                    'hall_id' => $hall_id
                ]);
                return $pay;
            } else {
                $message = "The hall does not exist.";
                return $message;
            }
        } else {
            $message = "you are not employee in the hall";
            return $message;
        }
    }

    public function updatepay(array $data, $id)
    {
        $pay = Paymentway::findOrFail($id);
        if ($pay) {
            $pay->update([
                'type' => $data['type'],
            ]);
            $pay->save();
            return $pay;
        } else {
            $message = "The payment way off hall  not found.";
            return $message;
        }
    }

    public function showPay($hallId) {
        return Paymentway::where('hall_id', $hallId)->get();

    }

}
