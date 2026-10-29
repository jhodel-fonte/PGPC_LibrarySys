<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Keep reference data available for tests that use RefreshDatabase. */
    protected $seed = true;
}
