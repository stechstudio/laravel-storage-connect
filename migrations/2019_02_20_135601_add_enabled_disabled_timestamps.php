<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnabledDisabledTimestamps extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cloud_storages', function (Blueprint $table) {
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {

    }
}