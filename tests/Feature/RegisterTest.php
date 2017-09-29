<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Log;

class RegisterTest extends TestCase
{

  /**
   * Fields are required on registration.
   *
   * @return void
   */
    public function testRequiredFieldsOnRegister()
    {
        $this->json('POST','/api/register')
            ->assertStatus(422)
            ->assertJson([
                'errors'=> [
                    'name' =>['The name field is required.'],
                    'email'=>['The email field is required.'],
                    'password'=>['The password field is required.'],
                ],
              ]);
    }

    /**
     * Password and password_confirmation must match.
     *
     * @return void
     */
    public function testRequiresPasswordConfirmation()
    {
        $payload = [
            'name' => 'testy',
            'email' => 'test@test123.com',
            'password' => 'test123',
        ];
        $this->json('POST','/api/register',$payload)
            ->assertStatus(422)
            ->assertJson([
                'errors'=> [
                    'password'=>['The password confirmation does not match.'],
                ],
            ]);
    }

    /**
     * User can register successfully.
     *
     * @return void
     */
    public function testRegistersSuccessfully()
    {
        $payload = [
            'name' => 'testy',
            'email' => 'test@test123.com',
            'password' => 'test123',
            'password_confirmation' => 'test123',
        ];
        $this->json('POST','/api/register',$payload)
            ->assertStatus(201)
            ->assertJsonStructure([
                'data'=> [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                    'api_token'
                ],
            ]);
    }

    /**
     * Emails must be unique.
     *
     * @return void
     */
    public function testUniqueRegistations()
    {
        $user = factory(User::class)->create();
        $payload = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'test123',
            'password_confirmation' => 'test123',
        ];
        $this->json('POST','/api/register',$payload)
            ->assertStatus(422)
            ->assertJson([
              "errors" => [
                 "email" => [
                     "The email has already been taken."
                 ]
              ]
        ]);
    }

}
