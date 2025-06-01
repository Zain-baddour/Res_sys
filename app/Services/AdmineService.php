<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\AppSettingO;
use App\Models\hall;
use App\Models\Hall_img;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdmineService
{
    public function getPendingHalls(){
        return hall::with('images')->where('status', 'pending')->get();
//            ->map(function ($hall){
//            return [
//                'id' => $hall->id,
//                'name' => $hall->name,
//                'owner_id' => $hall->owner_id,
//                'capacity' => $hall->capacity,
//                'location' => $hall->location,
//                'contact' => $hall->contact,
//                'type' => $hall->type,
//                'events' => $hall->events,
//                'hall_image' => $hall->hall_image,
//                'images' => $hall->images->map(function (Hall_img $image) {
//                    return $image->image_path;
//                }),
//            ];
//        });
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

    public function updateSettings(array $data) {
        $settings = AppSetting::first();
        if (!$settings) {
            $settings = AppSetting::create($data);
        }
        else {
            $settings->update($data);
        }
        return $settings;
    }

    public function getSettings () {
        return AppSetting::first();
    }

    public function updateOfficeSettings(array $data) {
        $settings = AppSettingO::first();
        if (!$settings) {
            $settings = AppSettingO::create($data);
        }
        else {
            $settings->update($data);
        }
        return $settings;
    }

    public function getOfficeSettings () {
        return AppSettingO::first();
    }

    public function getAllUsers() {
        return User::all();
    }

    public function getUserById($id) {
        return User::where('id', $id)->get();
    }

}
