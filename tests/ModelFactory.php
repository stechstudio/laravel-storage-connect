<?php

use Illuminate\Support\Str;

$factory->define(\STS\StorageConnect\Tests\TestUser::class, function( $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt('secret'),
        'remember_token' => Str::random(10),
    ];
});