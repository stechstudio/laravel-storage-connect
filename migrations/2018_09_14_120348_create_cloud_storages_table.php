<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCloudStoragesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cloud_storages', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('owner');
            $table->string('driver');
            $table->json('token')->nullable();

            $table->string('name')->nullable();
            $table->string('email')->nullable();

            $table->boolean('connected')->default(0);
            $table->boolean('enabled')->default(0);
            $table->boolean('full')->default(0);
            $table->string('reason')->nullable();

            $table->bigInteger('total_space')->nullable();
            $table->bigInteger('space_used')->nullable();
            $table->bigInteger('space_available')->nullable();
            $table->float('percent_full', 4)->default(0);


            $table->timestamp('space_checked_at')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cloud_storages');
    }
}