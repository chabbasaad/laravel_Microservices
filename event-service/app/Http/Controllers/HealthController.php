<?php

namespace App\Http\Controllers;

class HealthController extends Controller
{
    public function check()
    {
        return response()->json([
            'status' => 'healthy',
            'service' => 'event-service',
            'timestamp' => now()
        ]);
    }
}
