<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('serial_num', 30);
            $table->string('qrcode')->nullable();
            $table->integer('sender_id');
            $table->integer('receiver_id')->nullable();
            $table->integer('sender_address_id');
            $table->integer('receiver_address_id')->nullable();
            $table->integer('status')->default(0);
            $table->text('description')->nullable();
            $table->bigInteger('created_at_unix');
            $table->bigInteger('on_the_way_at_unix')->nullable();
            $table->bigInteger('expected_arrival_at_unix')->nullable();
            $table->timestamps();
            $table->engine('MyISAM');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
