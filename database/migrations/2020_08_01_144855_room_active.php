<?php

use App\Room;
use App\RoomActiveHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RoomActive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room', function (Blueprint $table) {
            $table->boolean('isActive')->default(true)->after('location');
        });

        Schema::create('room_active_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('room_id');
            $table->date('active_from');
            $table->date('active_to')->nullable();
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('room');
        });

        $rooms = Room::withTrashed()->get();
        foreach ($rooms as $room) {
            RoomActiveHistory::create([
                'active_from' => $room->created_at,
                'room_id' => $room->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
