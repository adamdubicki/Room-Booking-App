<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Log;

class RegisterTest extends TestCase
{

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
