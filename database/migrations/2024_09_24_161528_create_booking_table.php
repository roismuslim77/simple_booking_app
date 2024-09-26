<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('bookings');
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('room_id')->unsigned();
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->uuid('rateplan_id')->unsigned();
            $table->foreign('rateplan_id')->references('id')->on('rateplans')->onDelete('cascade');
            $table->uuid('calendar_id')->unsigned();
            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('cascade');
            $table->string('reservation_number');
            $table->date('reservation_date');
            $table->dateTime('check_in');
            $table->dateTime('check_out');
            $table->string('name');
            $table->string('email');
            $table->string('phone_number');
            $table->string('country');
            $table->double('total', 15, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'expired']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking');
    }
}
