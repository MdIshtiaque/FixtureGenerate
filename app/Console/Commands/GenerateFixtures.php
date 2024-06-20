<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateFixtures extends Command
{
    protected $signature = 'fixtures:generate';
    protected $description = 'Generate round-robin fixtures with specific constraints';

    public function handle()
    {
        $numTeams = 13;
        $numDays = 5;
        $timeSlots = ['09:20', '10:20', '11:20', '12:20', '13:20', '14:20', '15:20', '16:20'];
        $numCourts = 2;

        $fixtures = $this->generateFixtures($numTeams, $numDays, $timeSlots, $numCourts);

        echo json_encode($fixtures, JSON_PRETTY_PRINT);
    }

    private function generateFixtures($numTeams, $numDays, $timeSlots, $numCourts)
    {
        $fixtures = [];
        $totalGamesPerTeam = array_fill(1, $numTeams, 0);
        $gamesPerDayPerTeam = array_fill(1, $numTeams, array_fill(1, $numDays, 0));

        // Initialize round-robin matches
        $roundRobin = [];
        for ($i = 1; $i <= $numTeams; $i++) {
            for ($j = $i + 1; $j <= $numTeams; $j++) {
                $roundRobin[] = [$i, $j];
            }
        }

        shuffle($roundRobin);

        $timeSlotIndex = 0;
        $day = 1;
        $court = 1;

        foreach ($roundRobin as $match) {
            list($team1, $team2) = $match;

            // Ensure each team has at least 2 games per day and exactly 3 games on 2 dates
            while (
                $gamesPerDayPerTeam[$team1][$day] >= 2 ||
                $gamesPerDayPerTeam[$team2][$day] >= 2 ||
                $totalGamesPerTeam[$team1] >= 6 ||
                $totalGamesPerTeam[$team2] >= 6
            ) {
                $day++;
                if ($day > $numDays) {
                    $day = 1;
                    $court++;
                    if ($court > $numCourts) {
                        $court = 1;
                    }
                }
            }

            $fixtures[$day][] = [
                'team1' => $team1,
                'team2' => $team2,
                'court' => $court,
                'time' => $timeSlots[$timeSlotIndex % count($timeSlots)]
            ];

            $gamesPerDayPerTeam[$team1][$day]++;
            $gamesPerDayPerTeam[$team2][$day]++;
            $totalGamesPerTeam[$team1]++;
            $totalGamesPerTeam[$team2]++;
            $timeSlotIndex++;
        }

        return $fixtures;
    }
}
