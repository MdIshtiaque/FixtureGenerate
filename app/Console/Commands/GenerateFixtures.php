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
        $dates = 5;
        $gamesPerDate = [16, 16, 16, 16, 14]; // Total games (both courts) per date
        $courts = 2;
        $timeSlots = [
            '09:20', '10:20', '11:20', '12:20', '13:20', '14:20', '15:20', '16:20',
        ];

        $fixtures = [];
        $teamGamesPerDate = array_fill(1, 13, array_fill(0, $dates, 0));
        $matchups = [];

        // Generate a round-robin schedule
        for ($i = 0; $i < count($teams); $i++) {
            for ($j = $i + 1; $j < count($teams); $j++) {
                $matchups[] = [$teams[$i], $teams[$j]];
            }
        }

        shuffle($matchups);

        // First pass: schedule the matchups
        foreach ($matchups as $matchup) {
            list($team1, $team2) = $matchup;
            $scheduled = false;

            // Try to schedule the match on a suitable date
            for ($date = 0; $date < $dates; $date++) {
                if ($teamGamesPerDate[$team1][$date] < 3 && $teamGamesPerDate[$team2][$date] < 3 &&
                    $this->countGamesOnDate($fixtures, $date) < $gamesPerDate[$date]) {
                    $fixtures[$date][] = [
                        'team1' => $team1,
                        'team2' => $team2,
                        'court' => ($this->countGamesOnDate($fixtures, $date) % $courts) + 1,
                        'time' => $timeSlots[floor($this->countGamesOnDate($fixtures, $date) / $courts) % count($timeSlots)]
                    ];
                    $teamGamesPerDate[$team1][$date]++;
                    $teamGamesPerDate[$team2][$date]++;
                    $scheduled = true;
                    break;
                }
            }
            if (!$scheduled) {
                // Log unscheduled games, possibly implement more sophisticated rescheduling logic
            }
        }

        // Second pass: Ensure each team plays at least 2 games per date
        for ($date = 0; $date < $dates; $date++) {
            foreach ($teams as $team) {
                while ($teamGamesPerDate[$team][$date] < 2) {
                    // Attempt to find a new opponent and schedule a game
                    $foundMatch = false;
                    foreach ($teams as $opponent) {
                        if ($team != $opponent && $teamGamesPerDate[$opponent][$date] < 3 && $teamGamesPerDate[$team][$date] < 3) {
                            $alreadyPlayed = false;
                            foreach ($fixtures[$date] as $game) {
                                if (($game['team1'] == $team && $game['team2'] == $opponent) || ($game['team1'] == $opponent && $game['team2'] == $team)) {
                                    $alreadyPlayed = true;
                                    break;
                                }
                            }

                            if (!$alreadyPlayed) {
                                // Schedule this new match
                                $fixtures[$date][] = [
                                    'team1' => $team,
                                    'team2' => $opponent,
                                    'court' => ($this->countGamesOnDate($fixtures, $date) % $courts) + 1,
                                    'time' => $timeSlots[floor($this->countGamesOnDate($fixtures, $date) / $courts) % count($timeSlots)]
                                ];
                                $teamGamesPerDate[$team][$date]++;
                                $teamGamesPerDate[$opponent][$date]++;
                                $foundMatch = true;
                                break;
                            }
                        }
                    }

                    if (!$foundMatch) {
                        break; // If no match found, log the issue
                    }
                }
            }
        }

        // Ensure 3 games on exactly 2 dates for each team
        foreach ($teamGamesPerDate as $team => $datesPlayed) {
            $threeGameDates = array_keys(array_filter($datesPlayed, function ($games) {
                return $games == 3;
            }));

            if (count($threeGameDates) < 2) {
                $datesToAddGames = array_keys(array_filter($datesPlayed, function ($games) {
                    return $games < 3;
                }));

                shuffle($datesToAddGames);

                while (count($threeGameDates) < 2 && count($datesToAddGames) > 0) {
                    $date = array_pop($datesToAddGames);
                    $teamGamesPerDate[$team][$date] = 3;
                    $threeGameDates[] = $date;

                    foreach ($fixtures[$date] as &$game) {
                        if ($game['team1'] == $team || $game['team2'] == $team) {
                            $game['time'] = $timeSlots[count($fixtures[$date]) % count($timeSlots)];
                            break;
                        }
                    }
                }
            }
        }

        return $fixtures;
    }
    // example

    private function countGamesOnDate($fixtures, $date)
    {
        return isset($fixtures[$date]) ? count($fixtures[$date]) : 0;
    }
}
