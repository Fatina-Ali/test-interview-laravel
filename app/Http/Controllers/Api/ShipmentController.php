<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Http\Resources\ShipmentResource;
use App\Models\Address;
use App\Models\Client;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client as HttpClient;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
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
        try {
            $validated = $request->validate([
                'description'   => 'required',
                'receiver'  => 'required',
                'sender_address'    => 'required',
                'receiver_address'  => 'required',
                'created_at_unix'   => 'required|integer',
            ]);

            DB::beginTransaction();

            $serialNum = Shipment::getServiceNum();

            // Process receiver
            $receiver = $this->processClient($request->receiver);
            // Process receiver address
            $receiverAddress = $this->processAddress($request->receiver_address, $receiver);

            // Process sender
            $sender = Client::where('user_id', $this->user_id)->first();
            // Process sender address
            $senderAddress = $this->processAddress($request->sender_address, $sender);

            // Create shipment
            $shipment = Shipment::create([
                'serial_num' => $serialNum,
                'sender_id' => $sender->id,
                'description' => $validated['description'],
                'receiver_id' => $receiver->id,
                'sender_address_id' => $senderAddress->id,
                'receiver_address_id' => $receiverAddress->id,
                'created_at_unix' => $validated['created_at_unix'],
                'status' => 1,
            ]);

            DB::commit();

            return apiResponse(['shipment' => new ShipmentResource($shipment)]);
        } catch (Exception $e) {
            DB::rollBack();
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

    private function processClient($data)
    {
        if (empty($data['id'])) {
            return Client::create([
                'phone' => $data['phone'],
                'name' => $data['name'],
                'image' => 'uploads/images/user_default_image.png',
            ]);
        }

        return Client::findOrFail($data['id']);
    }

    private function processAddress($data, $client)
    {
        if (empty($data['id'])) {
            $country = Country::findOrFail($data['country']['id']);
            $this->checkCountryLimitations($country);

            if (Address::where('client_id', $client->id)->count() >= 100) {
                throw new Exception("You've reached out the maximum count of addresses!");
            }

            $formatted_address = $this->getFormattedAddress($data['latitude'], $data['longitude']);

            $address = Address::create([
                'name' => $data['name'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'location_description' => $formatted_address,
                'country_id' => $data['country']['id'],
                'client_id' => $client->id,
            ]);

            $address->indexByLocation();
            $address->setRelation('country', $country);

            return $address;
        }

        return Address::findOrFail($data['id']);
    }

    private function getFormattedAddress($latitude, $longitude)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}";

        $client = new HttpClient();
        $response = $client->get($url);
        $data = json_decode($response->getBody()->getContents(), true);

        return $data['results'][0]['formatted_address'] ?? null;
    }
    private function checkCountryLimitations($country){
        if(Shipment::whereHas('receiverAddress',function($query) use($country){
            $query->where('country_id',$country->id);
        })->whereRaw('DATE(created_at) = CURDATE()')->count() == 100){
            throw new Exception("Country daily limitations has already reached! please try again tomorrow");
        }
    }
}
