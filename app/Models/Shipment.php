<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Shipment extends Model
{
    use HasFactory;
    const STATUS = [
        '1' => 'Pending',
        '2' => 'On The Way',
        '3' => 'Paid',
        '4' => 'Delivered',
        '5' => 'Cancelled'
    ] ;
    protected $fillable = [
        'serial_num',
        'category_id',
        'qrcode',
        'sender_id',
        'receiver_id',
        'description',
        'sender_address_id',
        'receiver_address_id',
        'status',
        'created_at_unix',
        'on_the_way_at_unix',
        'expected_arrival_at_unix'

    ];


    static public function getServiceNum(){
        $last = self::orderBy('id','DESC')->first();
        if($last){
            $arr = explode('-', $last->serial_num);
            $serial = (int)$arr[1] + 1;
            return 'SH-'.$serial;
        }else{
            return 'SH-0';
        }
    }

    public function images()
    {
        return $this->hasMany(ShipmentImage::class);
    }


    public function receiver() {
        return $this->belongsTo(Client::class,'receiver_id');
    }

    public function sender() {
        return $this->belongsTo(Client::class,'sender_id');
    }

    public function receiverAddress() {
        return $this->belongsTo(Address::class,'receiver_address_id');
    }

    public function senderAddress() {
        return $this->belongsTo(Address::class,'sender_address_id');
    }


    static public function findBySerial($serial_num) {
        return self::where('serial_num',$serial_num)->first();
    }


}
