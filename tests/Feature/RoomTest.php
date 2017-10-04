<?php

namespace Tests\Feature;

use App\Meeting;
use App\User;
use App\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;



class RoomTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
     public function testsCanFetchRooms()
     {
         $user1 = factory(User::class)->create();
         $headers = ['Authorization' => "Bearer $user1->api_token"];
         $this->json('GET', '/api/rooms', [], $headers)
             ->assertStatus(200)
             ->assertJson([
                   "rooms"=> [
                         [
                             "id" => 1,
                         ],
                         [
                             "id"=> 2,
                         ],
                         [
                             "id"=> 3,
                         ],
                     ]
             ]);
    }

    public function testsCanGetMeetingsByRoom()
    {
        $user1 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1800);
        $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
        $date3 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 7200);
        $date4 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 10800);

        $meeting1 = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user1->id,
            'room_id' => 1,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);

        $meeting2 = factory(Meeting::class)->create([
            'name' => 'test name 2',
            'description' => 'test description 2',
            'user_id' => $user1->id,
            'room_id' => 1,
            'start_time' => $date3,
            'end_time' => $date4,
        ]);

        $meeting3 = factory(Meeting::class)->create([
            'name' => 'test name 3',
            'description' => 'test description 3',
            'user_id' => $user1->id,
            'room_id' => 2,
            'start_time' => $date3,
            'end_time' => $date4,
        ]);


        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $this->json('GET','/api/rooms/1/meetings', [], $headers)
            ->assertJson([
                "meetings"=> [
                  [
                      "room_id" => 1,
                      "name" => "test name 1",
                      "description"=> "test description 1",
                      "start_time" => "$date1",
                      "end_time" => "$date2",
                      "id" => 1
                  ],
                  [
                      "room_id" => 1,
                      "name" => "test name 2",
                      "description"=> "test description 2",
                      "start_time"=> "$date3",
                      "end_time"=> "$date4",
                      "id"=> 2
                  ],
                ]
            ])->assertStatus(200);

        $this->json('GET',"/api/rooms/1/meetings?before=$date3", [], $headers)
            ->assertJson([
              "meetings"=> [
                [
                    "room_id" => 1,
                    "name" => "test name 1",
                    "description"=> "test description 1",
                    "start_time" => "$date1",
                    "end_time" => "$date2",
                    "id" => 1
                ],
              ]
            ])->assertStatus(200);

        $this->json('GET',"/api/rooms/1/meetings?after=$date2", [], $headers)
            ->assertJson([
              "meetings"=> [
                [
                    "room_id" => 1,
                    "name" => "test name 2",
                    "description"=> "test description 2",
                    "start_time"=> "$date3",
                    "end_time"=> "$date4",
                    "id"=> 2
                ],
              ]
            ])->assertStatus(200);

        $this->json('GET',"/api/rooms/2/meetings", [], $headers)
            ->assertJson([
              "meetings"=> [
                [
                    "room_id" => 2,
                    "name" => "test name 3",
                    "description"=> "test description 3",
                    "start_time"=> "$date3",
                    "end_time"=> "$date4",
                    "id"=> 3
                ],
              ]
            ])->assertStatus(200);
    }
}
