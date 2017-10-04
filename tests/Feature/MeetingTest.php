<?php

namespace Tests\Feature;

use App\Meeting;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;


class MeetingTest extends TestCase
{

  /**
   * Test getting meetings and filtering by user and dates.
   *
   * @return void
   */
    public function testsCanFetchAndFilterMeetings()
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
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
            'user_id' => $user2->id,
            'room_id' => 2,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);

        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $this->json('GET','/api/meetings', [], $headers)
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

        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $this->json('GET',"/api/meetings?user_id=$user1->id", [], $headers)
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

        $this->json('GET',"/api/meetings?user_id=$user2->id", [], $headers)
            ->assertJson([
              "meetings"=> [
                [
                    "room_id" => 2,
                    "name" => "test name 3",
                    "description"=> "test description 3",
                    "start_time" => "$date1",
                    "end_time" => "$date2",
                    "id" => 3
                ],
              ]
            ])->assertStatus(200);

        $this->json('GET',"/api/meetings?user_id=$user1->id&before=$date3", [], $headers)
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

        $this->json('GET',"/api/meetings?user_id=$user1->id&after=$date2", [], $headers)
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
    }


    /**
     * Test can successfully create meetings.
     *
     * @return void
     */
    public function testsCanCreateMeetings()
    {
        $user1 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
        $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 7200);
        $payload = [
            "room_id" => 1,
            "name" => "test name 1",
            "description" => "test description 1",
            "start_time" => $date1,
            "end_time" => $date2,
        ];

        $this->json('POST', '/api/meetings', $payload, $headers)
            ->assertStatus(201)
            ->assertJson([
              "meeting"=> [
                    "room_id" => 1,
                    "name" => "test name 1",
                    "description"=> "test description 1",
                    "start_time" => "$date1",
                    "end_time" => "$date2",
                    "id" => 1
                    ]
              ]);
    }



    /**
     * Test the min and maximum duration constraints on meeting
     *
     * @return void
     */
    public function testsDurationConstraint()
    {
        $user1 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
        $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3610);
        $date3 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 28000);
        $payload = [
            "room_id" => 1,
            "name" => "test name 1",
            "description" => "test description 1",
            "start_time" => $date1,
            "end_time" => $date2,
        ];

        $this->json('POST', '/api/meetings', $payload, $headers)
            ->assertStatus(400)
            ->assertJson([
                "errors"=>[
                    "message" => ["Meeting duration must be atleast 15 minutes."]
                ]
            ]);

        $payload = [
            "room_id" => 1,
            "name" => "test name 1",
            "description" => "test description 1",
            "start_time" => $date1,
            "end_time" => $date3,
        ];

        $this->json('POST', '/api/meetings', $payload, $headers)
            ->assertStatus(400)
            ->assertJson([
                "errors"=>[
                    "message" => ["Meeting duration cannot exceed 180 minutes."]
                ]
            ]);
    }

    /**
     * Tests for conflicting meeting constraint.
     *
     * @return void
     */
    public function testsConflictConstraints()
    {
        $user1 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1800);
        $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
        $date3 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 4800);
        $date4 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 7200);

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

        $payload = [
            "room_id" => 1,
            "name" => "test name 3",
            "description" => "test description 3",
            "start_time" => date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1600),
            "end_time" => date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3500),
        ];

        $this->json('POST', '/api/meetings', $payload, $headers)
            ->assertStatus(400)
            ->assertJson([
                "errors"=>[
                    "conflicts" => [[
                          ["id"=>1],
                      ]]
                ]
            ]);

        $payload = [
            "room_id" => 1,
            "name" => "test name 3",
            "description" => "test description 3",
            "start_time" => date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1900),
            "end_time" => date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3500),
        ];

        $this->json('POST', '/api/meetings', $payload, $headers)
            ->assertStatus(400)
            ->assertJson([
                "errors"=>[
                    "conflicts" => [[
                          ["id"=>1],
                      ]]
                ]
            ]);

          $payload = [
              "room_id" => 1,
              "name" => "test name 3",
              "description" => "test description 3",
              "start_time" => date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1900),
              "end_time" => date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 4900),
          ];

          $this->json('POST', '/api/meetings', $payload, $headers)
              ->assertStatus(400)
              ->assertJson([
                  "errors"=>[
                      "conflicts" => [[
                            ["id" => 1],
                            ["id" => 2],
                        ]]
                  ]
              ]);

          $payload = [
              "room_id" => 1,
              "name" => "test name 3",
              "description" => "test description 3",
              "start_time" => date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1700),
              "end_time" => date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 7300),
          ];

          $this->json('POST', '/api/meetings', $payload, $headers)
              ->assertStatus(400)
              ->assertJson([
                  "errors"=>[
                      "conflicts" => [[
                            ["id" => 1],
                            ["id" => 2],
                        ]]
                  ]
              ]);
    }

    /**
     * Tests for deleting meetings.
     *
     * @return void
     */
    public function testsCanDeleteSuccessfully()
    {
        $user1 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1800);
        $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
        $meeting1 = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user1->id,
            'room_id' => 1,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);

        $this->json('DELETE', "/api/meetings/$meeting1->id", [], $headers)
            ->assertStatus(204);
        assert(!is_null($meeting1->deleted_at));
    }


    /**
     * Cannot delete other users meetings.
     *
     * @return void
     */
    public function testsCannotDeleteOtherUsersMeetings()
    {
        $user1 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1800);
        $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
        $meeting1 = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user1->id,
            'room_id' => 1,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);

        $user2 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user2->api_token"];

        $this->json('DELETE', "/api/meetings/$meeting1->id", [], $headers)
            ->assertStatus(403);
    }


    /**
     * Cannot delete other users meetings.
     *
     * @return void
     */
    public function testsCannotDeleteNonExistentMeeting()
    {
        $user1 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $this->json('DELETE', "/api/meetings/1", [], $headers)
            ->assertStatus(404);
    }


    /**
     * Cannot edit other users meetings.
     *
     * @return void
     */
    public function testsCannotEditOtherUsersMeetings()
    {
        $user1 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1800);
        $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
        $meeting1 = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user1->id,
            'room_id' => 1,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);

        $user2 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user2->api_token"];

        $this->json('PATCH', "/api/meetings/$meeting1->id", ["name"=>"new name"], $headers)
            ->assertStatus(403);
    }



    /**
     * Can edit meetings.
     *
     * @return void
     */
    public function testsCanEditSuccessfully()
    {
        $user1 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1800);
        $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
        $date3 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 4600);
        $meeting1 = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user1->id,
            'room_id' => 1,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);

        $payload = [
            "room_id" => 2,
            "name" => "new name",
            "description" => "new description",
            "start_time" => $date1,
            "end_time" => $date3,
        ];

        $this->json('PATCH', "/api/meetings/$meeting1->id", $payload, $headers)
            ->assertStatus(200)
            ->assertJson([
              "meeting"=> [
                    "room_id" => 2,
                    "name" => "new name",
                    "description"=> "new description",
                    "start_time" => "$date1",
                    "end_time" => "$date3",
                    "id" => 1
              ]
            ]);
    }


    /**
     * Cannot edit nonexistant meetings.
     *
     * @return void
     */
    public function testsCannotEditNonExistantMeetings()
    {
        $user1 = factory(User::class)->create();
        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1800);
        $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
        $payload = [
            "room_id" => 2,
            "name" => "new name",
            "description" => "new description",
            "start_time" => $date1,
            "end_time" => $date2,
        ];

        $this->json('PATCH', '/api/meetings/1', $payload, $headers)
            ->assertStatus(404);
    }

}
