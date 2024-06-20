<?php

namespace App\Http\Controllers;

use App\Services\FixtureGenerator;
use Illuminate\Http\Request;

class FixtureController extends Controller
{
    public function showFixtures()
    {
        $generator = new FixtureGenerator();
        $fixtures = $generator->generateFixtures();

        return view('fixtures', compact('fixtures'));
    }
}
