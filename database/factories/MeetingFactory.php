<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/
$factory->define(App\Meeting::class, function (Faker $faker) {
    return [
        'name' => $faker->text,
        'description' => $faker->paragraph,
        'user_id' => $faker->randomDigit,
        'room_id' => $faker->randomDigit,
        'start_time' => $faker->dateTime,
        'end_time' => $faker->dateTime,
    ];
});
