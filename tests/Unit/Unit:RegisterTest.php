<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Log;

class RegisterTest extends TestCase
{
    /**
     * Test for invalid registration submissions
     *
     * @return void
     */
    public function testInvalidRegistrations()
    {
      /**
       * name field is required
       */
      $response = $this->json('POST','/api/register',[
          'name' => '',
          'email' => 'test@test.com',
          'password' => 'test123',
          'password_confirmation' => 'test123',
      ]);

      $response->assertJson([
          'message'=>'The given data was invalid.',
          'errors'=>['name'=>['The name field is required.']]
      ])->assertStatus(422);

      /**
       * email field is required
       */
      $response = $this->json('POST','/api/register',[
          'name' => 'testy',
          'email' => '',
          'password' => 'test123',
          'password_confirmation' => 'test123',
      ]);

      $response->assertJson([
          'message'=>'The given data was invalid.',
          'errors'=>['email'=>['The email field is required.']]
      ])->assertStatus(422);

      /**
       * password field is required
       */
      $response = $this->json('POST','/api/register',[
          'name' => 'testy',
          'email' => 'test@test123.com',
          'password' => '',
          'password_confirmation' => 'test123',
      ]);

      $response->assertJson([
          'message'=>'The given data was invalid.',
          'errors'=>['password'=>['The password field is required.']]
      ])->assertStatus(422);


      /**
       * password confirmation field is required
       */
      $response = $this->json('POST','/api/register',[
          'name' => 'testy',
          'email' => 'test@test123.com',
          'password' => 'test123',
          'password_confirmation' => '',
      ]);

      $response->assertJson([
          'message'=>'The given data was invalid.',
          'errors'=>['password'=>['The password confirmation does not match.']]
      ])->assertStatus(422);

      /**
       * email field must be a valid email
       */
      $response = $this->json('POST','/api/register',[
          'name' => 'testy',
          'email' => 'test',
          'password' => 'test123',
          'password_confirmation' => 'test123',
      ]);


      $response->assertJson([
          'message'=>'The given data was invalid.',
          'errors'=>['email'=>['The email must be a valid email address.']]
      ])->assertStatus(422);
    }


    /**
     * Test that valid registrations go through
     *
     * @return void
     */
    public function testValidRegistrations()
    {
      /**
       * create a valid user
       */
      $response = $this->json('POST','/api/register',[
          'name' => 'testy',
          'email' => 'test@test123.com',
          'password' => 'test123',
          'password_confirmation' => 'test123',
      ]);

      $response->assertJson([
          'data'=>[
            'name'=>"testy",
            'email'=>'test@test123.com'
          ]
      ])->assertStatus(201);

    }

}
