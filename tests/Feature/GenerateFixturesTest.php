<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Console\Commands\GenerateFixtures;

class GenerateFixturesTest extends TestCase
{
    public function testFixturesGeneration()
    {
        $this->artisan('fixtures:generate')
            ->assertExitCode(0);
    }

    public function testEachTeamPlaysMinimumTwoGamesPerDate()
    {
        $fixtures = $this->getFixtures();
        $teamGamesPerDate = $this->getTeamGamesCountPerDate($fixtures);

        foreach ($teamGamesPerDate as $team => $dates) {
            foreach ($dates as $games) {
                $this->assertGreaterThanOrEqual(2, $games, "Team $team does not play at least 2 games on date");
            }
        }
    }

    public function testEachTeamPlaysThreeGamesOnTwoDates()
    {
        $fixtures = $this->getFixtures();
        $teamGamesPerDate = $this->getTeamGamesCountPerDate($fixtures);

        foreach ($teamGamesPerDate as $team => $dates) {
            $threeGameDates = array_filter($dates, function ($games) {
                return $games == 3;
            });
            $this->assertCount(2, $threeGameDates, "Team $team does not play exactly 3 games on 2 dates");
        }
    }

    private function getFixtures()
    {
        $command = new GenerateFixtures();
        return $command->generateFixtures();
    }

    private function getTeamGamesCountPerDate($fixtures)
    {
        $teamGamesPerDate = array_fill(1, 13, array_fill(0, 5, 0));

        foreach ($fixtures as $date => $games) {
            foreach ($games as $game) {
                $teamGamesPerDate[$game['team1']][$date]++;
                $teamGamesPerDate[$game['team2']][$date]++;
            }
        }

        return $teamGamesPerDate;
    }
}
