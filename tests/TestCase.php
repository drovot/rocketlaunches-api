<?php

use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    /**
     * @return Application
     */
    public function createApplication(): Application
    {
        return require __DIR__ . '/../app/app.php';
    }
}
