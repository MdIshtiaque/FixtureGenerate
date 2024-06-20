<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\FixtureGenerator;

class FixtureGeneratorTest extends TestCase
{
    public function testGenerateFixtures()
    {
        $generator = new FixtureGenerator();
        $fixtures = $generator->generateFixtures();

        $this->assertCount(5, $fixtures, "There should be 5 dates of games.");

        foreach ($fixtures as $date => $courts) {
            if ($date < 5) {
                $this->assertCount(8, $courts[1], "First 4 dates should have 8 games on each court.");
                $this->assertCount(8, $courts[2], "First 4 dates should have 8 games on each court.");
            } else {
                $this->assertCount(7, $courts[1], "Last date should have 7 games on each court.");
                $this->assertCount(7, $courts[2], "Last date should have 7 games on each court.");
            }
        }

        $teamGames = array_fill(1, 13, 0);
        foreach ($fixtures as $courts) {
            foreach ($courts as $games) {
                foreach ($games as $time => $game) {
                    $teamGames[$game[0]]++;
                    $teamGames[$game[1]]++;
                }
            }
        }

        foreach ($teamGames as $team => $games) {
            $this->assertGreaterThanOrEqual(10, $games, "Each team should play at least 10 games.");
            $this->assertLessThanOrEqual(12, $games, "Each team should play no more than 12 games.");
        }

        foreach ($teamGames as $team => $games) {
            $datesPlayed = 0;
            foreach ($fixtures as $date => $courts) {
                foreach ($courts as $games) {
                    foreach ($games as $time => $game) {
                        if (in_array($team, $game)) {
                            $datesPlayed++;
                            break;
                        }
                    }
                }
            }
            $this->assertGreaterThanOrEqual(2, $datesPlayed, "Each team must play a minimum of 2 games per date.");
            $this->assertGreaterThanOrEqual(2, $games, "Each team should play at least 2 games per date.");
        }
    }
}
