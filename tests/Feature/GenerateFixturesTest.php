<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Console\Commands\GenerateFixtures;

class GenerateFixturesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test basic functionality of fixture generation.
     *
     * @return void
     */
    public function testFixtureGeneration()
    {
        $command = new GenerateFixtures();
        $fixtures = $command->generateFixtures();

        $this->assertIsArray($fixtures);
        $this->assertCount(5, $fixtures); // Assuming 5 dates of games

        foreach ($fixtures as $date => $games) {
            $this->checkGameSpacing($games);
        }
    }

    /**
     * Helper function to test that no team plays another game within three hours.
     *
     * @param array $games Games played on a single date
     */
    private function checkGameSpacing(array $games)
    {
        $lastGameTimes = [];

        foreach ($games as $game) {
            $team1 = $game['team1'];
            $team2 = $game['team2'];
            $time = $game['time'];

            if (isset($lastGameTimes[$team1])) {
                $timeDiff = $this->calculateTimeDiff($lastGameTimes[$team1], $time);
                $this->assertTrue($timeDiff >= 180);
            }

            if (isset($lastGameTimes[$team2])) {
                $timeDiff = $this->calculateTimeDiff($lastGameTimes[$team2], $time);
                $this->assertTrue($timeDiff >= 180);
            }

            $lastGameTimes[$team1] = $time;
            $lastGameTimes[$team2] = $time;
        }
    }

    /**
     * Helper function to calculate the difference in minutes between two times.
     *
     * @param string $time1
     * @param string $time2
     * @return int Difference in minutes
     */
    private function calculateTimeDiff($time1, $time2)
    {
        $time1 = \Carbon\Carbon::createFromFormat('H:i', $time1);
        $time2 = \Carbon\Carbon::createFromFormat('H:i', $time2);
        return abs($time1->diffInMinutes($time2));
    }
}
