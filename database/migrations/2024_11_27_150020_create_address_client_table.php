<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_client', function (Blueprint $table) {
            $table->id(); // Optional, for primary key.
            $table->unsignedBigInteger('address_id'); // Foreign key to table1
            $table->unsignedBigInteger('client_id'); // Foreign key to table2

            // Set foreign keys
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

            $table->timestamps(); // Optional, for created_at and updated_at.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address_client');
    }
}
