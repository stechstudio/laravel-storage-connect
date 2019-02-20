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

        // Need to make sure we have some enabled_at date for all currently enabled connections
        \STS\StorageConnect\Models\CloudStorage::where('enabled', 1)->get()->each(function($storage) {
            $storage->update(['enabled_at' => $storage->created_at]);
        });

        // Same for disabled
        \STS\StorageConnect\Models\CloudStorage::where('enabled', 0)->get()->each(function($storage) {
            $storage->update(['disabled_at' => $storage->updated_at]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {

    }
}