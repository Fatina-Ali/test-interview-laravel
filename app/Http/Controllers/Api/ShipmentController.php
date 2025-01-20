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
use App\Models\Country;
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
     public function store(Request $request){



        $validatedData = $request->validate([
            'description'        => 'required',
            'receiver'           => 'required|array',
            'sender_address'     => 'required|array',
            'receiver_address'   => 'required|array',
            'created_at_unix'    => 'required|integer',
        ]);

        try{


            $shipment = DB::transaction(function () use ($validatedData) {

                $receiver = $this->createOrFindClient($validatedData['receiver']);


                $receiverAddress = $this->createOrFindAddress(
                    $validatedData['receiver_address'],
                    $receiver
                );
                $this->checkCountryLimitations($receiverAddress->country);

                $sender = Client::where('user_id', $this->user_id)->firstOrFail();

                $senderAddress = $this->createOrFindAddress(
                    $validatedData['sender_address'],
                    $sender
                );

                $shipment = Shipment::create([
                    'serial_num'          => Shipment::getServiceNum(),
                    'sender_id'           => $sender->id,
                    'description'         => $validatedData['description'],
                    'receiver_id'         => $receiver->id,
                    'sender_address_id'   => $senderAddress->id,
                    'receiver_address_id' => $receiverAddress->id,
                    'created_at_unix'     => $validatedData['created_at_unix'],
                    'status'              => 1,
                ]);

                return $shipment;
            });
            return apiResponse(['shipment' => new ShipmentResource($shipment)]);
        }
        catch(Exception $e){
            return apiResponse(null, $e->getMessage(), 400);
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


        private function createOrFindAddress(array $data, Client $client)
    {
        if (empty($data['id'])) {
            $country = Country::findOrFail($data['country']['id']);
            $address = Address::create([
                'name'       => $data['name'],
                'latitude'   => $data['latitude'],
                'longitude'  => $data['longitude'],
                'country_id' => $data['country']['id'],
            ]);
            $address->clients()->attach($client->id);
            $address->setRelation('country', $country);
            return $address;
        }

        return Address::findOrFail($data['id']);
    }

    private function createOrFindClient(array $data)
    {
        if (empty($data['id'])) {
            return Client::create([
                'phone' => $data['phone'],
                'name'  => $data['name'],
                // 'image'=>''
                // // 'image' => $data['image'] ?? 'uploads/images/user_default_image.png',
            ]);
        }

        return Client::findOrFail($data['id']);
    }
    private function checkCountryLimitations($country)
    {
        DB::transaction(function () use ($country) {
            $shipmentCount = Shipment::whereHas('receiverAddress', function ($query) use ($country) {
                $query->where('country_id', $country->id);
            })->whereRaw('DATE(created_at) = CURDATE()')
              ->select('id')
              ->lockForUpdate()
              ->count();

            if ($shipmentCount >= 100) {
                throw new Exception("The daily shipment limit for this country has been reached. Please try again tomorrow.");
            }
        });
    }
}
