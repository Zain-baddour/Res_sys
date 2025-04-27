<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use App\Services\HallService;

class HallController extends Controller
{
    protected $hallService;

    public function __construct(HallService $hallService)
    {
        $this->hallService = $hallService;
    }

    /**
     * Get all halls.
     */
    public function index()
    {
        $halls = $this->hallService->getAll();
        return response()->json($halls);
    }

    /**
     * Get a single hall by ID.
     */
    public function show($id)
    {
        $hall = $this->hallService->getById($id);
        return response()->json($hall);
    }

    /**
     * Create a new hall.
     */


    public function store(Request $request)
    {
        $data = $request->validate([
            'hall_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'contact' => 'required|string|max:255',
            'type' => 'required|string',
            'events' => 'required|array',
            'events.*' => 'string|in:wedding,graduation,birthday,engagement,funeral',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $hall = $this->hallService->create($data);
        return response()->json($hall);

    }

    /**
     * Update an existing hall.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'hall_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'contact' => 'sometimes|string|max:255',
            'type' => 'sometimes|string',
            'events' => 'sometimes|array',
            'events.*' => 'string|in:wedding,graduation,birthday,engagement,funeral',
        ]);

        $hall = $this->hallService->update($id, $data);
        return response()->json($hall);
    }

    /**
     * Delete a hall.
     */
    public function destroy($id)
    {
        $this->hallService->delete($id);
        return response()->json(['message' => 'Hall deleted successfully']);
    }

    public function getHallImagesC($hallId) {
        $images = $this->hallService->getHallImages($hallId);
        return response()->json([
            'hall_id' => $hallId,
            'images' => $images,
        ]);
    }

    public function getHallInquiries($hallId) {
        return response()->json($this->hallService->getInquiriesByHall($hallId));
    }

    public function getHallEmployees($hallId) {
        return response()->json($this->hallService->getHallEmployee($hallId));
    }

    public function delHallEmployees($employeeId) {
        $deleted = $this->hallService->delHallEmployee($employeeId);

        if ($deleted) {
            return response()->json(['message' => 'تم حذف الموظف بنجاح']);
        }
        return response()->json(['message' => 'حدث خطأ اثناء عملية الحذف']);
    }



    //***********************************
    //ZainHassan *******************

    public function addpolices(Request $request,$hall_id)
    {
        $data = $request->validate([
            'description'=>'string|max:255',
        ]);
        $polices = $this->hallService->addpolices($data,$hall_id);

        return response()->json([$polices],201);
    }

    public function updatepolices($id,Request $request ){
        $data =$request->validate([
            'description'=> 'nullable|string|max:255',
        ]);
        $polices = $this->hallService->updatepolices($id,$data);

        return response()->json($polices,201);
    }

    public function showpolices($id)
    {
        $polices = $this->hallService->getpolicesById($id);
        return response()->json($polices);
    }

    public function addoffer(Request $request,$hall_id)
    {
        $data = $request->validate([
            'period_offer'=>'required|date',
            'start_offer' => 'required|date',
            'description' => 'nullable|string',
            'offer_val'=>'required|decimal:2',
            'removable'=>'nullable|required|boolean',
           
        ]);
        $offers= $this->hallService->addoffer($data,$hall_id);
        return response()->json($offers, 201);
    }

    public function updateoffer($offer_id,Request $request){
$data =$request->validate([
    'period_offer'=>'required|date',
    'start_offer' => 'required|date',
    'description' => 'nullable|string',
    'offer_val'=>'required|decimal:2',
    'removable'=>'nullable|required|boolean',
        ]);
        $offer = $this->hallService->updateoffer($offer_id,$data);

        return response()->json($offer,201);
    }
    public function showoffer($id)
    {
        $offer = $this->hallService->getofferById($id);
        return response()->json($offer);
    }
    
    public function add_detail(Request $request,$hall_id)
    {
        $data = $request->validate([
            'type_hall' => 'required|string|in:wedding,sorrow both',
            'card_price'=>'required|json',
            'res_price'=>'required|integer|between:100,1000',
        ]);

        $detail= $this->hallService->add_detail($data,$hall_id);
        return response()->json($detail, 201);
    }


    public function showdetail($id)
    {
        $detail = $this->hallService->getdetailById($id);
        return response()->json($detail);
    }

    public function updatdet(Request $request ,$id){
        $data = $request->validate([
            'type_hall'=>'required|in:wedding,sorrow both',
            'card_price'=>'required|json',
            'res_price'=>'|integer|between:100,1000',
           
        ]);
        $detail =$this->hallService->updatedetail($data,$id);

        return response()->json($detail,201);

    }
    public function add_service(Request $request,$hall_id){
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price'=>'required|integer|between:2,12',
            'description'=>'required|string|max:255',
            
        ]);
        $service= $this->hallService->add_service($data,$hall_id);
        return response()->json($service, 201);
    }

    public function updatservice(Request $request ,$id){
        $data =$request->validate([
            'name' => 'required|string|max:255',
            'price'=>'required|integer|between:2,12',
            'description'=>'required|string|max:255',
        ]);
        $service =$this->hallService->updateservice($data,$id);

        return response()->json($service,201);

}
public function showservice($hall_id){
    $service = $this->hallService->showservice($hall_id);
    return response()->json($service);
}

public function add_time(Request $request,$hall_id){
    $data = $request->validate([
        'type' => 'required|string|in:morning,evening',
'from'=>'required|date_format:H:i',
        'to'=>'required|date_format:H:i'
    ]);
    $time= $this->hallService->add_time($data,$hall_id);
    return response()->json($time, 201);
}

public function updattime(Request $request ,$id){
    $data =$request->validate([
        'type' => 'required|string|in:morning,evening',
        'from'=>'required|date_format:H:i',
                'to'=>'required|date_format:H:i'  
    ]);
    $time =$this->hallService->updatetime($data,$id);

    return response()->json($time,201);
}

public function showtime($id)
{
    $time = $this->hallService->gettimeById($id);
    return response()->json($time);
}
}

