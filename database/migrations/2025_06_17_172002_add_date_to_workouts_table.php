<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->date('date')->after('client_id');
        });
    }

    public function down()
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }

};
