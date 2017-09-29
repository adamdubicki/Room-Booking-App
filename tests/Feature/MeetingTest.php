<?php

namespace Tests\Feature;

use App\Meeting;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

// 'name' => $faker->name,
// 'description' => $faker->description,
// 'user_id' => $faker->user_id,
// 'room_id' => $faker->room_id,
// 'start_time' => $faker->end_time,
// 'end_time' => $faker->start_time

class MeetingTest extends TestCase
{
    public function testsCanFetchAndFilterMeetings()
    {
        $user = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user->api_token"];
        $meeting = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user->id,
            'room_id' => 1,
            'start_time' => date("Y-m-d H:i"),
            'end_time' => date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600),
        ]);

        $this->json('GET','/api/meetings/{$user->id}', $headers)
            ->assertStatus(200);
    }
}
