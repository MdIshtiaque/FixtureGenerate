<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateFixtures extends Command
{
    protected $signature = 'fixtures:generate';
    protected $description = 'Generate the fixtures for the league';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $fixtures = $this->generateFixtures();
        $this->info(json_encode($fixtures, JSON_PRETTY_PRINT));
    }

    public function generateFixtures()
    {
        $teams = range(1, 13);
        $numDays = 5;
        $fixtures = [];

        // Define structured time slots
        $courtSchedule = [
            1 => ['09:20', '12:20', '15:20'],  // Court 1 schedule
            2 => ['10:20', '13:20', '16:20']   // Court 2 schedule
        ];

        // Games structure for the first 4 days and a separate structure for the last day
        $gameStructure = [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
            [2, 1, 4, 3, 6, 5, 8, 7, 10, 9, 12, 11, 1, 13, 3, 2, 5, 4, 7, 6, 9, 8, 11, 10, 13, 12]
        ];

        // Generate fixtures for each day
        for ($day = 0; $day < $numDays; $day++) {
            $fixtures[$day] = [];
            $dayMod = $day % 2;  // Alternate between two structures
            $maxGames = ($day == 4) ? 14 : 16;  // Fewer games on the last day

            for ($game = 0; $game < $maxGames; $game++) {
                $team1 = $gameStructure[$dayMod][$game * 2 % 26];  // Cycle through the structure
                $team2 = $gameStructure[$dayMod][$game * 2 % 26 + 1];
                $court = ($game % 2) + 1;
                $timeSlotIndex = intval($game / 2) % count($courtSchedule[$court]);

                $fixtures[$day][] = [
                    'team1' => $team1,
                    'team2' => $team2,
                    'court' => $court,
                    'time' => $courtSchedule[$court][$timeSlotIndex]
                ];
            }

            // Rotate teams for the next day to change the team matchups
            array_push($teams, array_shift($teams));
        }

        return $fixtures;
    }
}
