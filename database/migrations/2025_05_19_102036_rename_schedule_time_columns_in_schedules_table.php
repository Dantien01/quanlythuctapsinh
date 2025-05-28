<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->renameColumn('start_time', 'overall_start_date');
            $table->renameColumn('end_time', 'overall_end_date');
        });
    }

    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->renameColumn('overall_start_date', 'start_time');
            $table->renameColumn('overall_end_date', 'end_time');
        });
    }
};
