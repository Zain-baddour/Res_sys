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

    public function addpolices(Request $request)
    {
        $data = $request->validate([
            'description' => 'nullable|string|max:255',
            'hall_id'=>'|integer',
        ]);
        $polices = $this->hallService->addpolices($data);

        return response()->json([$polices],201);
    }
    public function updatepolices(Request $request ,$id){
        $data = $request->validate([
          
            'description' => 'nullable|string|max:255',
            'hall_id'=>'|integer',
        ]);
        $polices = $this->hallService->updatepolices($data,$id);

        return response()->json($polices,201);
    }
    public function showpolices($id)
    {
        $polices = $this->hallService->getpolicesById($id);
        return response()->json($polices);
    }

    public function addoffer(Request $request)
    {
        $data = $request->validate([
            'period_offer'=>'required|date',
            'start_ofer' => 'required|date',
            'description' => 'nullable|string',
            'offer_val'=>'required|decimal:2',
            'removable'=>'nullable|required|boolean',
            'hall_id'=>'required|integer',
        ]);
        $offers= $this->hallService->addoffer($data);
        return response()->json($offers, 201);
    }
    public function add_detail(Request $request)
    {
        $data = $request->validate([
            'type_hall' => 'required|string|max:255',
            'card_price'=>'required|integer|between:4,12',
            'res_price'=>'required|integer|between:100,1000',
            'hall_id'=>'required|integer',
        ]);

        $detail= $this->hallService->add_detail($data);
        return response()->json($detail, 201);
    }


    public function showdetail($id)
    {
        $detail = $this->hallService->getdetailById($id);
        return response()->json($detail);
    }

    public function updatdet(Request $request ,$id){
        $data = $request->validate([
            'type_hall'=>'string|max:255',
            'card_price'=>'|integer|between:4,12',
            'res_price'=>'|integer|between:100,1000',
            'hall_id'=>'|integer',
        ]);
        $detail =$this->hallService->updatedetail($data,$id);

        return response()->json($detail,201);

    }

}
