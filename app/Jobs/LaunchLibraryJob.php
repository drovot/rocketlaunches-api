<?php

namespace App\Jobs;

use App\Supplier\Supplier\LaunchLibrary;

class LaunchLibraryJob extends Job
{

    /**
     * Execute Launch Collection
     *
     * @return void
     * @throws \JsonException
     */
    public function __invoke(): void
    {
        $launchLibrary = new LaunchLibrary();
        $launchLibrary->requestUpcomingLaunches();
        $launchLibrary->requestPreviousLaunches();
    }
}
