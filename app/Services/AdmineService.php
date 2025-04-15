<?php

namespace App\Services;

use App\Models\hall;
use App\Models\Hall_img;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdmineService
{
    public function getPendingHalls(){
        return hall::with('images')->where('status', 'pending')->get()->map(function ($hall){
            return [
                'id' => $hall->id,
                'name' => $hall->name,
                'owner_id' => $hall->owner_id,
                'capacity' => $hall->capacity,
                'location' => $hall->location,
                'contact' => $hall->contact,
                'type' => $hall->type,
                'events' => $hall->events,
                'hall_image' => $hall->hall_image ? url($hall->hall_image) : null,
                'images' => $hall->images->map(function (Hall_img $image) {
                    return url($image->image_path);
                }),
            ];
        });
    }

    public function updateHallStatus($hallId, $status){
        $hall = hall::findOrFail($hallId);
        if(!in_array($status,['approved','rejected'])){
            throw new \InvalidArgumentException('status is not Right');
        }

        $hall->status = $status;
        $hall->save();
        return $hall;
    }

}
