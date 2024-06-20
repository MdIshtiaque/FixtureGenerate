<?php

namespace App\Services;

class FixtureGenerator
{
    private $teams;
    private $dates;
    private $gamesPerDate;
    private $lastDateGames;
    private $games;
    private $timeSlots;

    public function __construct($teams = 13, $dates = 5, $gamesPerDate = 8, $lastDateGames = 7)
    {
        $this->teams = range(1, $teams);
        $this->dates = $dates;
        $this->gamesPerDate = $gamesPerDate;
        $this->lastDateGames = $lastDateGames;
        $this->games = [];
        $this->timeSlots = $this->generateTimeSlots();
    }

    private function generateTimeSlots()
    {
        $timeSlots = [];
        $startHour = 9;
        $startMinute = 20;

        for ($hour = $startHour; $hour <= 16; $hour++) {
            for ($minute = $startMinute; $minute <= 20; $minute += 60) {
                $timeSlots[] = sprintf("%02d:%02d", $hour, $minute);
            }
        }

        return $timeSlots;
    }

    public function generateFixtures()
    {
        $fixtures = [];
        $teamGames = array_fill(1, count($this->teams), 0);

        for ($date = 1; $date <= $this->dates; $date++) {
            $gamesCount = ($date === $this->dates) ? $this->lastDateGames : $this->gamesPerDate;

            for ($court = 1; $court <= 2; $court++) {
                for ($game = 1; $game <= $gamesCount; $game++) {
                    $timeSlot = $this->timeSlots[($game - 1) % count($this->timeSlots)];
                    $fixtures[$date][$court][$timeSlot] = $this->getNextMatch($teamGames);
                }
            }
        }

        return $fixtures;
    }

    private function getNextMatch(&$teamGames)
    {
        $teams = $this->teams;
        shuffle($teams);

        foreach ($teams as $team1) {
            foreach ($teams as $team2) {
                if ($team1 != $team2 && !$this->isGamePlayed($team1, $team2)) {
                    $this->games[] = [$team1, $team2];
                    $teamGames[$team1]++;
                    $teamGames[$team2]++;
                    return [$team1, $team2];
                }
            }
        }
    }

    private function isGamePlayed($team1, $team2)
    {
        foreach ($this->games as $game) {
            if (($game[0] == $team1 && $game[1] == $team2) || ($game[0] == $team2 && $game[1] == $team1)) {
                return true;
            }
        }

        return false;
    }
}
