<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ShipmentResource;
use App\Models\Address;
use App\Models\Client;
use App\Models\Shipment;
use App\Models\ShipmentImage;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShipmentController extends Controller
{
    protected $user_id;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user_id = auth('sanctum')->user()->id ?? null;
            // Proceed with the request
            return $next($request);
        });
    }


    public function index()
    {

        $shipments = Shipment::where('sender_id',auth()->user()->client->id)->get();
        return apiResponse(ShipmentResource::collection($shipments));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       try{
            DB::beginTransaction();
            $request->validate([
                'description'   => 'required',
                'receiver'  => 'required',
                'sender_address'    => 'required',
                'receiver_address'  => 'required',
                'created_at_unix'   => 'required|integer'
            ]);
            $serialNum =  Shipment::getServiceNum();

            if(empty($request->receiver['id'])){
                $receiver = Client::create([
                    'phone' => $request->receiver['phone'],
                    'name'  => $request->receiver['name'],
                    'image' => 'uploads/images/user_default_image.png'
                ]);
            }else{
                $receiver = Client::findOrFail($request->receiver['id']);
            }
            if(empty($request->receiver_address['id'])){
                $receiverCountry = \App\Models\Country::find($request->receiver_address['country']['id']);
                $this->checkCountryLimitations($receiverCountry);
                $receiverAddress = Address::create([
                    'name' => $request->receiver_address['name'],
                    'latitude'  => $request->receiver_address['latitude'],
                    'longitude'  => $request->receiver_address['longitude'],
                    'country_id'    => $request->receiver_address['country']['id'],
                ]);
                $receiverAddress->clients()->attach($receiver->id);
                $receiverAddress->setRelation('country',$receiverCountry);
            }else{
                $receiverAddress = Address::findOrFail($request->receiver_address['id']);
                $this->checkCountryLimitations($receiverAddress->country);
            }
            $sender = Client::where('user_id',$this->user_id)->first();
            if(empty($request->sender_address['id'])){
                $senderCountry = \App\Models\Country::find($request->sender_address['country']['id']);
                $senderAddress = Address::create([
                    'name' => $request->sender_address['name'],
                    'latitude'  => $request->sender_address['latitude'],
                    'longitude'  => $request->sender_address['longitude'],
                    'country_id'    => $request->sender_address['country']['id'],
                ]);
                $senderAddress->clients()->attach($sender->id);
                $senderAddress->setRelation('country',$senderCountry);
            }else{
                $senderAddress = Address::findOrFail($request->receiver_address['id']);
            }

            $shipment = Shipment::create([
                'serial_num'    => $serialNum,
                'sender_id'       => $sender->id,
                'description'       => $request->description ?? '',
                'receiver_id'       => $receiver->id,
                'sender_address_id' => $senderAddress->id,
                'receiver_address_id'  => $receiverAddress->id,
                'created_at_unix'   => $request->created_at_unix,
                'status'            => 1
            ]);

            $shipment->setRelation('receiver',$receiver);
            $shipment->setRelation('sender',$sender);
            $shipment->setRelation('receiverAddress',$receiverAddress);
            $shipment->setRelation('senderAddress',$senderAddress);
            DB::commit();
            return apiResponse(['shipment'  => new ShipmentResource($shipment)]);
       }catch(Exception $e){
            DB::rollBack();
            return \apiResponse(null,$e->getMessage(),400);
       }





    }

    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        try {
            // Find the address by ID, or throw an exception if not found
            $sh = Shipment::findOrFail($id);
            // Return the response if the address is found
            return apiResponse(ShipmentResource::make($sh));
        } catch (ModelNotFoundException $e) {
            // Return a response if the address is not found
            return apiResponse(null, 'shipment not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shipment $shipment)
    {
        if ($this->user_id !== $shipment->sender_id) {
            return apiResponse(null, __('You can only edit your own shipment.'), 403);
        }
        $shipment->update($request->only(['receiver_id', 'status','category_id','description']));

        return  apiResponse(new ShipmentResource($shipment));
    }

    function qrCode(Request $request) {
        try{
            $shipment = Shipment::findBySerial($request->serial_num);
            if($request->qr_code){
                $shipment->qrcode = $request->qr_code;
                $shipment->status = 2;
                $shipment->on_the_way_at_unix = Carbon::now('UTC')->timestamp * 1000;
                $shipment->save();
            }
            return \apiResponse(['shipment' => $shipment]);
        }catch(Exception $e){
            return \apiResponse(null,$e->getMessage(),400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shipment $shipment)
    {
        if ($this->user_id !== $shipment->sender_id) {
            return apiResponse([], __('You can only delete your own Shipment.'), 403);
        }
        $shipment->images->delete();
        $shipment->delete();
        return  apiResponse(null,__('Deleted Successfully'));
    }


    private function checkCountryLimitations($country){
        if(Shipment::whereHas('receiverAddress',function($query) use($country){
            $query->where('country_id',$country->id);
        })->whereRaw('DATE(created_at) = CURDATE()')->count() == 100){
            throw new Exception("Country daily limitations has already reached! please try again tomorrow");
        }
    }
}
