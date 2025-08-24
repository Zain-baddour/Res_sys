<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\hall;
use App\Models\hall_employee;
use App\Models\Hall_img;
use App\Models\staff_requests;
use App\Notifications\StaffRequestApprovedNotification;
use App\Notifications\StaffRequestRejectedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OwnerService
{
    public function myHallSer(){
        //return hall::where('owner_id' , Auth::id())->get();
        return hall::with('images')->where('owner_id' , Auth::id())->get()->map(function ($hall){
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
    }

    public function getStaffRequests() {
        $myHalls = $this->myHallSer();
        $hallIds = $myHalls->pluck('id');

        return staff_requests::with('user:id,name,photo,email,number') // جلب بيانات المستخدم
        ->whereIn('hall_id', $hallIds)
            ->orderBy('created_at', 'desc') // لترتيب الطلبات حسب الأحدث
            ->get();
    }

    public function approveOrRejectStaff($staffReqId,$status) {
        $staffReq = staff_requests::findOrFail($staffReqId);
        $emp = hall_employee::where('user_id',$staffReq->user_id)->get();
        if ($emp) {
            return "this user has been already employed";
        }

        if ($staffReq->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'You cannot modify this request , its not pending.'
            ]);
        }
        if(!in_array($status,['approved','rejected'])){
            throw new \InvalidArgumentException('status is not Right');
        }

        $staffReq->update(['status' => $status]);

        if ($staffReq->status == 'approved'){
            $hall = Hall::findOrFail($staffReq->hall_id);

            if (!$hall->employees->contains($staffReq->user_id)) {
                $hall->employees()->attach($staffReq->user_id);
            }

            // إرسال إشعار للمستخدم المقبول
            $clientTokens = DeviceToken::where('user_id', $staffReq->user_id)->pluck('device_token');

            $firebase = new FirebaseNotificationService();

            foreach ($clientTokens as $token) {
                $firebase->sendNotification(
                    $token,
                    "Staff request approved",
                    "Your Staff request for hall :{$hall->name}  has been approved."
                );
            }
        }
        else{
            $hall = Hall::findOrFail($staffReq->hall_id);
            $clientTokens = DeviceToken::where('user_id', $staffReq->user_id)->pluck('device_token');

            $firebase = new FirebaseNotificationService();

            foreach ($clientTokens as $token) {
                $firebase->sendNotification(
                    $token,
                    "Staff request Rejected",
                    "Your Staff request for hall :{$hall->name}  has been Rejected."
                );
            }
        }

        return $staffReq;

    }

}
