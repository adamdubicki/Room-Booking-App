<?php

namespace Tests\Feature;

use App\Meeting;
use App\User;
use App\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class InvitationTest extends TestCase
{
    /**
     * Can create invitations
     *
     * @return void
     */
    public function testsCanCreateInvitations()
    {
          $user1 = factory(User::class)->create();
          $user2 = factory(User::class)->create();
          $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
          $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3610);
          $meeting1 = factory(Meeting::class)->create([
              'name' => 'test name 1',
              'description' => 'test description 1',
              'user_id' => $user1->id,
              'room_id' => 1,
              'start_time' => $date1,
              'end_time' => $date2,
          ]);
          $payload = [
              "user_id"=>$user2->id
          ];
          $headers = ['Authorization' => "Bearer $user1->api_token"];
          $this->json('POST',"/api/meetings/$meeting1->id/invitations", $payload, $headers)
              ->assertJson([
                    "invitation"=>[
                        "user_id" => $user2->id,
                        "meeting_id"=> $meeting1->id,
                        "status"=>"pending"
                    ]
              ])->assertStatus(200);
    }

    /**
     * Cannot invite themselves
     *
     * @return void
     */
    public function testsCannotInviteThemselves()
    {
          $user1 = factory(User::class)->create();
          $user2 = factory(User::class)->create();
          $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
          $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3610);
          $meeting1 = factory(Meeting::class)->create([
              'name' => 'test name 1',
              'description' => 'test description 1',
              'user_id' => $user1->id,
              'room_id' => 1,
              'start_time' => $date1,
              'end_time' => $date2,
          ]);
          $payload = [
              "user_id"=>$user1->id
          ];
          $headers = ['Authorization' => "Bearer $user1->api_token"];
          $this->json('POST',"/api/meetings/$meeting1->id/invitations", $payload, $headers)
              ->assertJson([
                    "errors"=>[
                        "message" => ["User cannot invite themself to their own meeting."]
                    ]
              ])->assertStatus(400);
    }


    /**
     * Meeting must exist
     *
     * @return void
     */
    public function testsMeetingMustExist()
    {
          $user1 = factory(User::class)->create();
          $user2 = factory(User::class)->create();
          $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
          $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3610);
          $payload = [
              "user_id"=>$user2->id
          ];
          $headers = ['Authorization' => "Bearer $user1->api_token"];
          $this->json('POST',"/api/meetings/1/invitations", $payload, $headers)
              ->assertJson([
                    "errors"=>[
                      "meeting_id"=> ["The selected meeting id is invalid."],
                    ]
              ])->assertStatus(400);
    }



    /**
     * Users cannot be invited twice
     *
     * @return void
     */
    public function testsCannotInviteUserTwice()
    {
          $user1 = factory(User::class)->create();
          $user2 = factory(User::class)->create();
          $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
          $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3610);
          $meeting1 = factory(Meeting::class)->create([
              'name' => 'test name 1',
              'description' => 'test description 1',
              'user_id' => $user1->id,
              'room_id' => 1,
              'start_time' => $date1,
              'end_time' => $date2,
          ]);
          $payload = [
              "user_id"=>$user2->id
          ];
          $headers = ['Authorization' => "Bearer $user1->api_token"];
          $this->json('POST',"/api/meetings/$meeting1->id/invitations", $payload, $headers)
              ->assertJson([
                    "invitation"=>[
                        "user_id" => $user2->id,
                        "meeting_id"=> $meeting1->id,
                        "status"=>"pending"
                    ]
              ])->assertStatus(200);

          $this->json('POST',"/api/meetings/$meeting1->id/invitations", $payload, $headers)
              ->assertJson([
                "errors"=>[
                  "user_id"=> ["User has already been added to meeting."],
                ]
              ])->assertStatus(400);
    }

    /**
     * Users cannot be invited twice
     *
     * @return void
     */
    public function testsMustOwnMeetingToInviteUsers()
    {
          $user1 = factory(User::class)->create();
          $user2 = factory(User::class)->create();
          $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
          $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3610);
          $meeting1 = factory(Meeting::class)->create([
              'name' => 'test name 1',
              'description' => 'test description 1',
              'user_id' => $user1->id,
              'room_id' => 1,
              'start_time' => $date1,
              'end_time' => $date2,
          ]);
          $payload = [
              "user_id"=>$user2->id
          ];
          $headers = ['Authorization' => "Bearer $user2->api_token"];
          $this->json('POST',"/api/meetings/$meeting1->id/invitations", $payload, $headers)
              ->assertJson([
                    "errors"=>[
                        "message" => ["User does not have permission to invite other users to meeting."],
                    ]
              ])->assertStatus(400);
    }

    /**
     * Invited user must exist.
     *
     * @return void
     */
    public function testsInviteeMustExist()
    {
          $user1 = factory(User::class)->create();
          $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
          $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3610);
          $meeting1 = factory(Meeting::class)->create([
              'name' => 'test name 1',
              'description' => 'test description 1',
              'user_id' => $user1->id,
              'room_id' => 1,
              'start_time' => $date1,
              'end_time' => $date2,
          ]);
          $payload = [
              "user_id"=>2
          ];
          $headers = ['Authorization' => "Bearer $user1->api_token"];
          $this->json('POST',"/api/meetings/$meeting1->id/invitations", $payload, $headers)
              ->assertJson([
                "errors"=>[
                  "user_id"=> ["The selected user id is invalid."],
                ]
              ])->assertStatus(400);
    }

    /**
     * Test that invitations can be deleted
     *
     * @return void
     */
    public function testsUninviteUser()
    {
          $user1 = factory(User::class)->create();
          $user2 = factory(User::class)->create();
          $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
          $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3610);
          $meeting1 = factory(Meeting::class)->create([
              'name' => 'test name 1',
              'description' => 'test description 1',
              'user_id' => $user1->id,
              'room_id' => 1,
              'start_time' => $date1,
              'end_time' => $date2,
          ]);
          $payload = [
              "user_id"=>2
          ];
          $headers = ['Authorization' => "Bearer $user1->api_token"];
          $this->json('POST',"/api/meetings/$meeting1->id/invitations", $payload, $headers)
              ->assertStatus(200);
          $this->json('DELETE',"/api/meetings/$meeting1->id/invitations/$user2->id", $payload, $headers)
              ->assertStatus(204);
    }

    /**
     * Invitees cannot delete meetings.
     *
     * @return void
     */
    public function testsMustBeMeetingOwnerToUninvite()
    {
          $user1 = factory(User::class)->create();
          $user2 = factory(User::class)->create();
          $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
          $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3610);
          $meeting1 = factory(Meeting::class)->create([
              'name' => 'test name 1',
              'description' => 'test description 1',
              'user_id' => $user1->id,
              'room_id' => 1,
              'start_time' => $date1,
              'end_time' => $date2,
          ]);
          $invitation1 = factory(Invitation::class)->create([
              'user_id' => $user2->id,
              'meeting_id' => $meeting1->id,
              'status' => 'pending'
          ]);
          $headers = ['Authorization' => "Bearer $user2->api_token"];
          $this->json('DELETE',"/api/meetings/$meeting1->id/invitations/$user2->id", [], $headers)
              ->assertStatus(403);
    }

    /**
     * Meeting creators can view meetings, invitees can view meetings.
     *
     * @return void
     */
    public function testsCanGetInvitationsOnBothSides()
    {
      $user1 = factory(User::class)->create();
      $user2 = factory(User::class)->create();
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
      $meeting2 = factory(Meeting::class)->create([
          'name' => 'test name 2',
          'description' => 'test description 2',
          'user_id' => $user2->id,
          'room_id' => 2,
          'start_time' => $date1,
          'end_time' => $date2,
      ]);
      $invitation1 = factory(Invitation::class)->create([
          'user_id' => $user2->id,
          'meeting_id' => $meeting1->id,
          'status' => 'pending'
      ]);
      $invitation2 = factory(Invitation::class)->create([
          'user_id' => $user1->id,
          'meeting_id' => $meeting2->id,
          'status' => 'pending'
      ]);
      $headers = ['Authorization' => "Bearer $user1->api_token"];
      $this->json('GET', "/api/meetings/$meeting1->id/invitations", [], $headers)
          ->assertStatus(200)
          ->assertJson([
            "invitations"=> [
              [
              "user_id" => 2,
              "meeting_id" => 1,
              "status"=> "pending",
              "id" => 1
              ]
            ]
          ]);

      $headers = ['Authorization' => "Bearer $user2->api_token"];
      $this->json('GET', "/api/meetings/$meeting2->id/invitations", [], $headers)
          ->assertStatus(200)
          ->assertJson([
            "invitations"=> [
              [
              "user_id" => 1,
              "meeting_id" => 2,
              "status"=> "pending",
              "id" => 2
              ]
            ]
          ]);

      $headers = ['Authorization' => "Bearer $user1->api_token"];
      $this->json('GET', "/api/invitations", [], $headers)
          ->assertStatus(200)
          ->assertJson([
            "invitations"=> [
              [
              "user_id" => 1,
              "meeting_id" => 2,
              "status"=> "pending",
              "id" => 2
              ]
            ]
          ]);
      }

    /**
     * Invitations can be queried by status.
     *
     * @return void
     */
    public function testsCanGetInvitationsByStatus()
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1800);
        $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
        $meeting1 = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user2->id,
            'room_id' => 1,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);
        $meeting2 = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user2->id,
            'room_id' => 1,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);
        $meeting3 = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user2->id,
            'room_id' => 1,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);
        $meeting4 = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user2->id,
            'room_id' => 1,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);
        $invitation1 = factory(Invitation::class)->create([
            'user_id' => $user1->id,
            'meeting_id' => $meeting1->id,
            'status' => 'accepted'
        ]);
        $invitation2 = factory(Invitation::class)->create([
            'user_id' => $user1->id,
            'meeting_id' => $meeting2->id,
            'status' => 'pending'
        ]);
        $invitation3 = factory(Invitation::class)->create([
            'user_id' => $user1->id,
            'meeting_id' => $meeting3->id,
            'status' => 'rejected'
        ]);
        $invitation4 = factory(Invitation::class)->create([
            'user_id' => $user1->id,
            'meeting_id' => $meeting4->id,
            'status' => 'cancelled'
        ]);

        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $this->json('GET', "/api/invitations?status=cancelled", [], $headers)
            ->assertStatus(200)
            ->assertJson([
              "invitations"=> [
                [
                "user_id" => 1,
                "meeting_id" => 4,
                "status"=> "cancelled",
                ]
              ]
            ]);
        $this->json('GET', "/api/invitations?status=rejected", [], $headers)
            ->assertStatus(200)
            ->assertJson([
              "invitations"=> [
                [
                "user_id" => 1,
                "meeting_id" => 3,
                "status"=> "rejected",
                ]
              ]
            ]);
        $this->json('GET', "/api/invitations?status=pending", [], $headers)
            ->assertStatus(200)
            ->assertJson([
              "invitations"=> [
                [
                "user_id" => 1,
                "meeting_id" => 2,
                "status"=> "pending",
                ]
              ]
            ]);
        $this->json('GET', "/api/invitations?status=accepted", [], $headers)
            ->assertStatus(200)
            ->assertJson([
              "invitations"=> [
                [
                "user_id" => 1,
                "meeting_id" => 1,
                "status"=> "accepted",
                ]
              ]
            ]);
    }


    /**
     * Invited users can accept or reject invitations that are not cancelled.
     *
     * @return void
     */
    public function testsCanAcceptAndRejectValidMeetings()
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $date1 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 1800);
        $date2 = date("Y-m-d H:i", strtotime(date("Y-m-d H:i")) + 3600);
        $meeting1 = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user2->id,
            'room_id' => 1,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);
        $meeting2 = factory(Meeting::class)->create([
            'name' => 'test name 1',
            'description' => 'test description 1',
            'user_id' => $user2->id,
            'room_id' => 1,
            'start_time' => $date1,
            'end_time' => $date2,
        ]);
        $invitation1 = factory(Invitation::class)->create([
            'user_id' => $user1->id,
            'meeting_id' => $meeting1->id,
            'status' => 'pending'
        ]);
        $invitation2 = factory(Invitation::class)->create([
            'user_id' => $user1->id,
            'meeting_id' => $meeting2->id,
            'status' => 'cancelled'
        ]);
        $headers = ['Authorization' => "Bearer $user1->api_token"];
        $payload = ['status'=>'accepted'];
        $this->json('PATCH', "/api/invitations/1", $payload, $headers)
            ->assertJson([
              "invitation"=> ["status"=> "accepted",]
            ])->assertStatus(200);
        $payload = ['status'=>'rejected'];
        $this->json('PATCH', "/api/invitations/1", $payload, $headers)
            ->assertJson([
              "invitation"=> ["status"=> "rejected",]
            ])->assertStatus(200);
        $this->json('PATCH', "/api/invitations/2", $payload, $headers)
            ->assertStatus(400);
      }

}
