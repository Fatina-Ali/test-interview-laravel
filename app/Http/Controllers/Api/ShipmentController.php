<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ShipmentResource;
use App\Models\Address;
use App\Models\Client;
use App\Models\Country;
use App\Models\Shipment;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShipmentController extends Controller
{
    protected $user_id;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user_id = auth('sanctum')->user()->id ?? null;
            return $next($request);
        });
    }

    public function index()
    {
        $shipments = Shipment::where('sender_id', auth()->user()->client->id)->get();
        return apiResponse(ShipmentResource::collection($shipments));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description'        => 'required',
            'receiver'           => 'required|array',
            'sender_address'     => 'required|array',
            'receiver_address'   => 'required|array',
            'created_at_unix'    => 'required|integer',
        ]);

        try {
            $shipment = DB::transaction(function () use ($validatedData) {
                $receiver = $this->handleClient($validatedData['receiver']);
                $receiverAddress = $this->handleAddress($validatedData['receiver_address'], $receiver, true);

                $sender = Client::where('user_id', $this->user_id)->firstOrFail();
                $senderAddress = $this->handleAddress($validatedData['sender_address'], $sender);

                return Shipment::create([
                    'serial_num'          => Shipment::getServiceNum(),
                    'sender_id'           => $sender->id,
                    'description'         => $validatedData['description'],
                    'receiver_id'         => $receiver->id,
                    'sender_address_id'   => $senderAddress->id,
                    'receiver_address_id' => $receiverAddress->id,
                    'created_at_unix'     => $validatedData['created_at_unix'],
                    'status'              => 1,
                ]);
            });

            return apiResponse(['shipment' => new ShipmentResource($shipment)]);
        } catch (Exception $e) {
            return apiResponse(null, $e->getMessage(), 400);
        }
    }

    public function show($id)
    {
        try {
            $shipment = Shipment::findOrFail($id);
            return apiResponse(ShipmentResource::make($shipment));
        } catch (ModelNotFoundException $e) {
            return apiResponse(null, 'Shipment not found', 404);
        }
    }

    public function update(Request $request, Shipment $shipment)
    {
        if ($this->user_id !== $shipment->sender_id) {
            return apiResponse(null, __('You can only edit your own shipment.'), 403);
        }

        $shipment->update($request->only(['receiver_id', 'status', 'category_id', 'description']));
        return apiResponse(new ShipmentResource($shipment));
    }

    public function qrCode(Request $request)
    {
        try {
            $shipment = Shipment::findBySerial($request->serial_num);

            if ($request->qr_code) {
                $shipment->qrcode = $request->qr_code;
                $shipment->status = 2;
                $shipment->on_the_way_at_unix = Carbon::now('UTC')->timestamp * 1000;
                $shipment->save();
            }

            return apiResponse(['shipment' => $shipment]);
        } catch (Exception $e) {
            return apiResponse(null, $e->getMessage(), 400);
        }
    }

    public function destroy(Shipment $shipment)
    {
        if ($this->user_id !== $shipment->sender_id) {
            return apiResponse([], __('You can only delete your own Shipment.'), 403);
        }

        $shipment->images->delete();
        $shipment->delete();

        return apiResponse(null, __('Deleted Successfully'));
    }

    private function handleAddress(array $data, Client $client, $checkLimitations = false)
    {
        if (empty($data['id'])) {
            $country = Country::findOrFail($data['country']['id']);

            if ($checkLimitations) {
                $this->checkCountryLimitations($country);
            }

            $formattedAddress = $this->getFormattedAddress($data['latitude'], $data['longitude']);

            $address = Address::create([
                'name'               => $data['name'],
                'latitude'           => $data['latitude'],
                'longitude'          => $data['longitude'],
                'location_description' => $formattedAddress,
                'country_id'         => $data['country']['id'],
                'client_id'          => $client->id,
            ]);

            $address->indexByLocation();
            $address->setRelation('country', $country);

            return $address;
        }

        return Address::findOrFail($data['id']);
    }

    private function handleClient(array $data)
    {
        if (empty($data['id'])) {
            return Client::create([
                'phone' => $data['phone'],
                'name'  => $data['name'],
                'image' => $data['image'] ?? 'uploads/images/user_default_image.png',
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
              ->lockForUpdate()
              ->count();

            if ($shipmentCount >= 100) {
                throw new Exception("The daily shipment limit for this country has been reached. Please try again tomorrow.");
            }
        });
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
}
