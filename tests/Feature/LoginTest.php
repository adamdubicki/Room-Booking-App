<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{

  /**
   * Fields are required on login.
   *
   * @return void
   */
    public function testRequiresEmailAndPassword()
    {
          $this->json('POST','/api/login')
              ->assertStatus(422)
              ->assertJson([
                  'errors'=> [
                      'email'=>['The email field is required.'],
                      'password'=>['The password field is required.'],
                  ],
              ]);
    }

    /**
     * Users can login successfully.
     *
     * @return void
     */
    public function testUserLogsInSuccessfully()
    {
          $user = factory(User::class)->create([
              'email' => 'test@test.com',
              'password' => bcrypt('test123'),
          ]);
          $payload = [
              'email' => 'test@test.com',
              'password' => 'test123',
          ];
          $this->json('POST','/api/login',$payload)
              ->assertStatus(200)
              ->assertJsonStructure([
                "data" => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                    'api_token',
                ],
              ]);
    }
}
