<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp()
    {
      parent::setUp();
      Artisan::call('migrate');
    }

    public function tearDown()
    {
      Artisan::call('migrate:reset');
      parent::tearDown();
    }
}
