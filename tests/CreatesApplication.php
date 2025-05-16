<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php'; // Đảm bảo đường dẫn này đúng

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}