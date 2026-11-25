<?php

namespace App\Http\Controllers;

class PerformanceController extends Controller
{
    public function __invoke()
    {
        $path = base_path('loadtest/results.json');
        $results = is_file($path) ? json_decode(file_get_contents($path), true) : null;

        return view('performance', ['results' => $results, 'locustfile' => @file_get_contents(base_path('loadtest/locustfile.py'))]);
    }
}
